<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.google.api_key');
    }

    /**
     * Generate embeddings for text using Gemini
     */
    public function createEmbedding(string $text, string $model = 'text-embedding-004'): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/{$model}:embedContent?key={$this->apiKey}", [
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]);

            if ($response->failed()) {
                throw new Exception('Google API request failed: ' . $response->body());
            }

            $data = $response->json();
            
            return [
                'vector' => $data['embedding']['values'],
                'model' => $model,
                'dimensions' => count($data['embedding']['values']),
            ];
        } catch (Exception $e) {
            Log::error('Google Embedding Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate chat completion using Gemini
     */
    public function createChatCompletion(array $messages, string $model = 'gemini-1.5-flash'): array
    {
        $startTime = microtime(true);

        try {
            // Handle system instruction if present
            $systemInstruction = null;
            if (!empty($messages) && $messages[0]['role'] === 'system') {
                $systemInstruction = array_shift($messages);
            }

            // Convert OpenAI-style messages to Gemini format
            $contents = array_map(function ($msg) {
                return [
                    'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [
                        ['text' => $msg['content']]
                    ]
                ];
            }, $messages);

            // If we have a system instruction, prepend it to the first user message
            if ($systemInstruction && !empty($contents)) {
                $contents[0]['parts'][0]['text'] = "System Instruction: " . $systemInstruction['content'] . "\n\n" . $contents[0]['parts'][0]['text'];
            } elseif ($systemInstruction) {
                // If no user message follows, just send it as user (edge case)
                $contents[] = [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemInstruction['content']]
                    ]
                ];
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/{$model}:generateContent?key={$this->apiKey}", [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->failed()) {
                Log::error('Google API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Google API request failed: ' . $response->body());
            }

            $data = $response->json();
            $responseTime = microtime(true) - $startTime;
            
            // Validate response structure
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                Log::error('Unexpected Google API response structure', [
                    'response' => $data
                ]);
                throw new Exception('Unexpected API response structure');
            }
            
            $content = $data['candidates'][0]['content']['parts'][0]['text'];
            $tokenCount = $data['usageMetadata']['totalTokenCount'] ?? 0;

            return [
                'content' => $content,
                'model' => $model,
                'tokens_used' => $tokenCount,
                'response_time' => round($responseTime, 2),
                'finish_reason' => $data['candidates'][0]['finishReason'] ?? 'unknown',
            ];
        } catch (Exception $e) {
            Log::error('Google Chat Completion Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Count tokens (approximate)
     */
    public function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
    }
}
