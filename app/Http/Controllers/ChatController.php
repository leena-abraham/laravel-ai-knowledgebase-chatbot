<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    private ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Public chat endpoint for customers
     */
    public function chat(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)
            ->where('is_active', true)
            ->firstOrFail();

        if (!$company->canCreateChat()) {
            return response()->json([
                'error' => 'Chat limit reached for this month.'
            ], 429);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email',
        ]);

        // Get or create session
        $sessionId = $validated['session_id'] ?? $this->chatbotService->generateSessionId();
        
        $chat = $this->chatbotService->getOrCreateChat($company, $sessionId, [
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
        ]);

        // Process message
        try {
            $response = $this->chatbotService->processMessage($chat, $validated['message']);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'response' => $response['response'],
                'sources' => $response['sources'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Chat processing error: ' . $e->getMessage(), [
                'company_id' => $chat->company_id,
                'session_id' => $sessionId,
                'message' => $validated['message'],
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to process message. Please try again.',
                'session_id' => $sessionId,
            ], 500);
        }
    }

    /**
     * Admin view of all chats
     */
    public function index()
    {
        $company = auth()->user()->company;
        $chats = $company->chats()
            ->with('messages')
            ->latest('started_at')
            ->paginate(20);

        return view('dashboard.chats.index', compact('chats'));
    }

    /**
     * View specific chat conversation
     */
    public function show($id)
    {
        $chat = auth()->user()->company->chats()
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            }])
            ->findOrFail($id);

        return view('dashboard.chats.show', compact('chat'));
    }

    /**
     * Provide feedback on a message
     */
    public function feedback(Request $request, $messageId)
    {
        $validated = $request->validate([
            'helpful' => 'required|boolean',
            'feedback' => 'nullable|string|max:500',
        ]);

        $message = auth()->user()->company->messages()->findOrFail($messageId);
        $message->markAsHelpful($validated['helpful'], $validated['feedback'] ?? null);

        return response()->json(['success' => true]);
    }
}
