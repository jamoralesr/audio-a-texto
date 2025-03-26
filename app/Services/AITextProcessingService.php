<?php

namespace App\Services;

use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Illuminate\Support\Facades\Log;

class AITextProcessingService
{
    public function processWithOpenAI(string $prompt, int $maxTokens = 570, ?string $systemPrompt = null): string
    {
        try {
            Log::info('Iniciando procesamiento con OpenAI', [
                'prompt_length' => strlen($prompt),
                'max_tokens' => $maxTokens
            ]);
            
            $builder = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o')
                ->withMaxTokens($maxTokens)
                ->withPrompt($prompt)
                ->withClientOptions(['timeout' => 120]);
                
            // Si se proporciona un systemPrompt, usarlo
            if ($systemPrompt) {
                $builder->withSystemPrompt($systemPrompt);
            }
            
            $response = $builder->asText();

            if (!$response || !isset($response->text)) {
                Log::error('OpenAI response is empty or invalid', ['response' => $response]);
                return 'Error';
            }

            return $response->text;
        } catch (\Exception $e) {
            Log::error('Error processing with OpenAI: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'Error';
        }
    }

    public function processWithClaude(string $prompt, string $systemPrompt, int $maxTokens = 570): string
    {
        try {
            $response = Prism::text()
                ->using(Provider::Anthropic, 'claude-3-5-sonnet-20241022')
                ->withSystemPrompt($systemPrompt)
                ->withMaxTokens($maxTokens)
                ->withPrompt($prompt)
                ->withClientOptions(['timeout' => 120])
                ->asText();

            if (!$response || !isset($response->text)) {
                Log::error('Claude response is empty or invalid', ['response' => $response]);
                return '';
            }

            return $response->text;
        } catch (\Exception $e) {
            Log::error('Error processing with Claude: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return '';
        }
    }

    public function processWithOllama(string $prompt, string $systemPrompt, int $maxTokens = 570): string
    {
        try {
            $response = Prism::text()
                ->using(Provider::Ollama, 'llama3.1:latest')
                ->withSystemPrompt($systemPrompt)
                ->withMaxTokens($maxTokens)
                ->withPrompt($prompt)
                ->withClientOptions(['timeout' => 120])
                ->asText();

            if (!$response || !isset($response->text)) {
                Log::error('Ollama response is empty or invalid', ['response' => $response]);
                return '';
            }

            return $response->text;
        } catch (\Exception $e) {
            Log::error('Error processing with Ollama: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return '';
        }
    }
}
