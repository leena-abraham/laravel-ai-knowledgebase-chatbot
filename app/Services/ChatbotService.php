<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Company;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ChatbotService
{
    private GeminiService $aiService;
    private DocumentProcessorService $documentProcessor;

    public function __construct(
        GeminiService $aiService,
        DocumentProcessorService $documentProcessor
    ) {
        $this->aiService = $aiService;
        $this->documentProcessor = $documentProcessor;
    }

    /**
     * Process user message and generate response
     */
    public function processMessage(Chat $chat, string $userMessage): array
    {
        // Add user message to chat
        $chat->addMessage('user', $userMessage);

        // Search for relevant context
        $relevantChunks = $this->documentProcessor->searchRelevantChunks(
            $userMessage,
            $chat->company_id,
            5
        );

        // Build context from chunks
        $context = $this->buildContext($relevantChunks);

        // Get conversation history
        $conversationHistory = $this->getConversationHistory($chat);

        // Build messages for AI
        $messages = $this->buildAIMessages($context, $conversationHistory, $userMessage);

        // Generate AI response
        $aiResponse = $this->aiService->createChatCompletion($messages);

        // Save assistant message
        $assistantMessage = $chat->addMessage('assistant', $aiResponse['content'], [
            'context_chunks' => array_map(fn($c) => $c['chunk']->id, $relevantChunks),
            'model_used' => $aiResponse['model'],
            'tokens_used' => $aiResponse['tokens_used'],
            'response_time' => $aiResponse['response_time'],
        ]);

        // Increment company chat count
        $chat->company->incrementChatCount();

        return [
            'message' => $assistantMessage,
            'response' => $aiResponse['content'],
            'sources' => $this->formatSources($relevantChunks),
        ];
    }

    /**
     * Create or get chat session
     */
    public function getOrCreateChat(Company $company, string $sessionId, array $metadata = []): Chat
    {
        $chat = Chat::where('session_id', $sessionId)->first();

        if (!$chat) {
            $chat = Chat::create([
                'company_id' => $company->id,
                'session_id' => $sessionId,
                'customer_name' => $metadata['customer_name'] ?? null,
                'customer_email' => $metadata['customer_email'] ?? null,
                'customer_ip' => $metadata['customer_ip'] ?? request()->ip(),
                'user_agent' => $metadata['user_agent'] ?? request()->userAgent(),
                'started_at' => now(),
            ]);
        }

        return $chat;
    }

    /**
     * Build context from relevant chunks
     */
    private function buildContext(array $relevantChunks): string
    {
        if (empty($relevantChunks)) {
            return '';
        }

        $contextParts = [];
        foreach ($relevantChunks as $item) {
            $contextParts[] = "From document '{$item['document']->title}':\n{$item['chunk']->content}";
        }

        return implode("\n\n---\n\n", $contextParts);
    }

    /**
     * Get conversation history
     */
    private function getConversationHistory(Chat $chat, int $limit = 10): array
    {
        $messages = $chat->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })->toArray();
    }

    /**
     * Build messages array for AI
     */
    private function buildAIMessages(string $context, array $history, string $currentMessage): array
    {
        // 1. Define the strict system prompt
        $systemPrompt = "You are an AI support agent. ";
        
        if (!empty($context)) {
            $systemPrompt .= "Use ONLY the provided context to answer the user's question. " .
                           "If the context does not contain the answer, reply exactly: " .
                           "\"I'm not sure about that, but I'll ask a human agent to assist.\"\n\n" .
                           "Context:\n{$context}";
        } else {
            // No documents found at all
            $systemPrompt .= "You do not have any knowledge base information yet. " .
                           "Reply exactly: \"I'm not sure about that, but I'll ask a human agent to assist.\"";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add conversation history (excluding current message)
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        // Current message is already in history, so we don't add it again

        return $messages;
    }

    /**
     * Format sources for response
     */
    private function formatSources(array $relevantChunks): array
    {
        $sources = [];
        foreach ($relevantChunks as $item) {
            $sources[] = [
                'document_title' => $item['document']->title,
                'similarity' => round($item['similarity'], 3),
                'excerpt' => Str::limit($item['chunk']->content, 150),
            ];
        }
        return $sources;
    }

    /**
     * Generate unique session ID
     */
    public function generateSessionId(): string
    {
        return Str::uuid()->toString();
    }
}
