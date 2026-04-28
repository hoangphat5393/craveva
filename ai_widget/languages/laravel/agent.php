<?php
// Laravel Integration for Craveva AI Agent
// Add to your Laravel controller or service

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CravevaAgentService
{
    protected $agentId;
    protected $apiKey;
    protected $baseUrl;
    
    public function __construct()
    {
        $this->agentId = '69ccc35e7d0ece6ff702487b';
        $this->apiKey = 'cvd_viPEIg-8UsVWKmO7hqfu4LZYxee4AoXE';
        $this->baseUrl = 'https://ai.craveva.com';
    }
    
    /**
     * Send a message to the agent
     *
     * @param string $message
     * @param string|null $userId
     * @param string|null $companyId
     * @param string|null $outletId
     * @return array
     */
    public function chat(string $message, ?string $userId = null, ?string $companyId = null, ?string $outletId = null): array
    {
        $endpoint = $this->baseUrl . '/api/v1/agents/' . $this->agentId . '/chat';
        
        $payload = [
            'message' => $message,
        ];
        
        if ($userId) {
            $payload['user_id'] = $userId;
        }
        if ($companyId) {
            $payload['company_id'] = $companyId;
        }
        if ($outletId) {
            $payload['outlet_id'] = $outletId;
        }
        
        $headers = [
            'Content-Type' => 'application/json',
        ];
        
        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }
        
        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($endpoint, $payload);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('Craveva Agent API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return ['error' => 'Failed to get response from agent'];
        } catch (\Exception $e) {
            Log::error('Craveva Agent Exception', [
                'message' => $e->getMessage(),
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Send a message and return only the output text
     *
     * @param string $message
     * @return string
     */
    public function getResponse(string $message): string
    {
        $response = $this->chat($message);
        return $response['output'] ?? 'Sorry, I could not process that request.';
    }
}

// Usage in a Controller:
// 
// use App\Services\CravevaAgentService;
// 
// public function chat(Request $request)
// {
//     $agent = new CravevaAgentService();
//     $response = $agent->chat($request->input('message'));
//     return response()->json($response);
// }
