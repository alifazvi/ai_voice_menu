<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIController
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    /**
     * Upload file to OpenAI
     */
    public function uploadFile(string $filePath, string $purpose = 'assistants'): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])
        ->attach(
            'file',
            fopen($filePath, 'r'),
            basename($filePath)
        )
        ->post('https://api.openai.com/v1/files', [
            'purpose' => $purpose,
        ]);

        if ($response->successful()) {
            return $response->json()['id'] ?? null;
        }

        Log::error('OpenAI file upload failed', [
            'status' => $response->status(),
            'body'   => $response->body()
        ]);

        return null;
    }

    /**
     * Attach file to specific Vector Store
     */
    public function attachFileToVectorStore(string $vectorStoreId, string $fileId): bool
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post("https://api.openai.com/v1/vector_stores/{$vectorStoreId}/files", [
            'file_id' => $fileId,
        ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('Attach file to vector store failed', [
            'vector_store_id' => $vectorStoreId,
            'status'          => $response->status(),
            'body'            => $response->body()
        ]);

        return false;
    }

    /**
     * Search inside specific Vector Store
     */
    public function search(string $vectorStoreId, string $query, int $maxResults = 3): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post("https://api.openai.com/v1/vector_stores/{$vectorStoreId}/search", [
            'query' => $query,
            'max_num_results' => $maxResults,
        ]);

        if (!$response->successful()) {
            Log::error('Vector store search failed', [
                'vector_store_id' => $vectorStoreId,
                'status'          => $response->status(),
                'body'            => $response->body()
            ]);
            return [];
        }

        return $response->json()['data'] ?? [];
    }

    /**
     * Full flow: Upload + Attach
     */
    public function uploadAndIndex(string $vectorStoreId, string $filePath): bool
    {
        $fileId = $this->uploadFile($filePath);

        if (!$fileId) {
            return false;
        }

        return $this->attachFileToVectorStore($vectorStoreId, $fileId);
    }
}