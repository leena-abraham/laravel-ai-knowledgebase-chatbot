<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Chunk;
use App\Models\Embedding;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentProcessorService
{
    private GeminiService $aiService;

    public function __construct(GeminiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Process uploaded document
     */
    public function processDocument(Document $document): void
    {
        try {
            $document->markAsProcessing();

            // Extract text from document
            $text = $this->extractText($document);
            $document->update(['content' => $text]);

            // Split into chunks
            $chunks = $this->splitIntoChunks($text);

            // Create chunks and embeddings
            foreach ($chunks as $index => $chunkText) {
                $chunk = Chunk::create([
                    'document_id' => $document->id,
                    'company_id' => $document->company_id,
                    'content' => $chunkText,
                    'chunk_index' => $index,
                    'token_count' => $this->aiService->estimateTokens($chunkText),
                ]);

                // Generate embedding
                $embeddingData = $this->aiService->createEmbedding($chunkText);

                Embedding::create([
                    'chunk_id' => $chunk->id,
                    'company_id' => $document->company_id,
                    'vector' => $embeddingData['vector'],
                    'model' => $embeddingData['model'],
                    'dimensions' => $embeddingData['dimensions'],
                ]);
            }

            $document->update(['chunk_count' => count($chunks)]);
            $document->markAsCompleted();

        } catch (Exception $e) {
            Log::error('Document Processing Error: ' . $e->getMessage(), [
                'document_id' => $document->id,
            ]);
            $document->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract text from document based on file type
     */
    private function extractText(Document $document): string
    {
        $filePath = Storage::path($document->file_path);

        switch ($document->file_type) {
            case 'pdf':
                return $this->extractFromPdf($filePath);
            
            case 'txt':
                return file_get_contents($filePath);
            
            case 'doc':
            case 'docx':
                return $this->extractFromWord($filePath);
            
            default:
                throw new Exception('Unsupported file type: ' . $document->file_type);
        }
    }

    /**
     * Extract text from PDF
     */
    private function extractFromPdf(string $filePath): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        } catch (Exception $e) {
            // Fallback: try to read as plain text
            return file_get_contents($filePath);
        }
    }

    /**
     * Extract text from Word document
     */
    private function extractFromWord(string $filePath): string
    {
        // For simplicity, treating as text. In production, use proper library
        return file_get_contents($filePath);
    }

    /**
     * Split text into chunks
     */
    private function splitIntoChunks(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        $chunks = [];
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $currentChunk = '';
        
        foreach ($sentences as $sentence) {
            if (strlen($currentChunk) + strlen($sentence) > $chunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                
                // Keep overlap from previous chunk
                $words = explode(' ', $currentChunk);
                $overlapWords = array_slice($words, -($overlap / 5)); // Approximate word count
                $currentChunk = implode(' ', $overlapWords) . ' ';
            }
            
            $currentChunk .= $sentence . ' ';
        }
        
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }
        
        return $chunks;
    }

    /**
     * Search for relevant chunks using semantic search
     */
    public function searchRelevantChunks(string $query, int $companyId, int $limit = 5): array
    {
        // Generate embedding for query
        $queryEmbedding = $this->aiService->createEmbedding($query);
        $queryVector = $queryEmbedding['vector'];

        // Get all embeddings for the company
        $embeddings = Embedding::where('company_id', $companyId)
            ->with('chunk.document')
            ->get();

        // Calculate similarities
        $results = [];
        foreach ($embeddings as $embedding) {
            $similarity = $this->cosineSimilarity($queryVector, $embedding->vector);
            
            $results[] = [
                'chunk' => $embedding->chunk,
                'similarity' => $similarity,
                'document' => $embedding->chunk->document,
            ];
        }

        // Sort by similarity and return top results
        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        
        return array_slice($results, 0, $limit);
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        foreach ($vectorA as $i => $value) {
            $dotProduct += $value * $vectorB[$i];
            $magnitudeA += $value * $value;
            $magnitudeB += $vectorB[$i] * $vectorB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
}
