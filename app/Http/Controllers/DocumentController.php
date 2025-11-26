<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentProcessorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class DocumentController extends Controller
{
    use AuthorizesRequests; // ← ADD THIS
    private DocumentProcessorService $documentProcessor;

    public function __construct(DocumentProcessorService $documentProcessor)
    {
        $this->documentProcessor = $documentProcessor;
    }

    public function index()
    {
        $company = auth()->user()->company;
        $documents = $company->documents()->with('uploader')->latest()->paginate(20);

        return view('dashboard.documents.index', compact('documents', 'company'));
    }

    public function create()
    {
        $company = auth()->user()->company;

        if (!$company->canUploadDocument()) {
            return redirect()->route('documents.index')
                ->with('error', 'Document limit reached. Please upgrade your plan.');
        }

        return view('dashboard.documents.create');
    }

    public function store(Request $request)
    {
        $company = auth()->user()->company;

        if (!$company->canUploadDocument()) {
            return back()->with('error', 'Document limit reached. Please upgrade your plan.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,txt,doc,docx|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . $company->id, 'local');

        $document = Document::create([
            'company_id' => $company->id,
            'uploaded_by' => auth()->id(),
            'title' => $validated['title'],
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);

        // Process document asynchronously (in production, use a queue)
        try {
            $this->documentProcessor->processDocument($document);
            $message = 'Document uploaded and processed successfully!';
        } catch (\Exception $e) {
            $message = 'Document uploaded but processing failed. Please try again.';
        }

        return redirect()->route('documents.index')->with('success', $message);
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        $document->load(['chunks', 'uploader']);

        return view('dashboard.documents.show', compact('document'));
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        // Delete file
        if ($document->file_path) {
            Storage::delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
    public function retry(Document $document)
    {
        $this->authorize('update', $document);

        if ($document->status !== 'failed') {
            return back()->with('error', 'Only failed documents can be retried.');
        }

        $document->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        try {
            $this->documentProcessor->processDocument($document);
            $message = 'Document processing started successfully!';
        } catch (\Exception $e) {
            $message = 'Processing failed again: ' . $e->getMessage();
        }

        return back()->with('success', $message);
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if ($query) {
            $companyId = auth()->user()->company_id;
            $results = $this->documentProcessor->searchRelevantChunks($query, $companyId, 5);
        }

        return view('dashboard.documents.search', compact('results', 'query'));
    }
}
