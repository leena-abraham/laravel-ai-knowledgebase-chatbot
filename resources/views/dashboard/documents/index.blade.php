@extends('layouts.dashboard')

@section('page-title', 'Knowledge Base')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <p style="color: #6b7280;">
            {{ $documents->total() }} documents | {{ $company->max_documents - $documents->total() }} slots remaining
        </p>
    </div>
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        📤 Upload Document
    </a>
</div>

<div class="card">
    @if($documents->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Chunks</th>
                    <th>Status</th>
                    <th>Uploaded</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $document)
                <tr>
                    <td>
                        <strong>{{ $document->title }}</strong>
                        <br>
                        <small style="color: #6b7280;">{{ $document->original_filename }}</small>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ strtoupper($document->file_type) }}</span>
                    </td>
                    <td>{{ $document->file_size_human }}</td>
                    <td>{{ $document->chunk_count }}</td>
                    <td>
                        @if($document->status === 'completed')
                            <span class="badge badge-success">✓ Processed</span>
                        @elseif($document->status === 'processing')
                            <span class="badge badge-warning">⏳ Processing</span>
                        @elseif($document->status === 'failed')
                            <span class="badge badge-danger">✗ Failed</span>
                        @else
                            <span class="badge badge-info">⏸ Pending</span>
                        @endif
                    </td>
                    <td>
                        {{ $document->created_at->diffForHumans() }}
                        <br>
                        <small style="color: #6b7280;">by {{ $document->uploader->name }}</small>
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-primary">
                                View
                            </a>
                            <form method="POST" action="{{ route('documents.destroy', $document) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete this document?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 24px;">
            {{ $documents->links() }}
        </div>
    @else
        <div style="text-align: center; padding: 64px 32px;">
            <div style="font-size: 64px; margin-bottom: 16px;">📚</div>
            <h3 style="color: #1f2937; margin-bottom: 8px;">No documents yet</h3>
            <p style="color: #6b7280; margin-bottom: 24px;">
                Upload your first document to start building your AI knowledge base
            </p>
            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                Upload Your First Document
            </a>
        </div>
    @endif
</div>
@endsection
