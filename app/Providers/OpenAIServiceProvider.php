<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI;
use GuzzleHttp\Client as GuzzleClient;

class OpenAIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(OpenAI\Client::class, function ($app) {
            $apiKey = config('openai.api_key');
            $organization = config('openai.organization');
            $timeout = config('openai.request_timeout', 120);
            
            // Crear cliente de OpenAI con configuración personalizada
            $clientOptions = [
                'api_key' => $apiKey,
                'timeout' => $timeout,
            ];
            
            if ($organization) {
                $clientOptions['organization'] = $organization;
            }
            
            return OpenAI::factory()
                ->withApiKey($apiKey)
                ->withOrganization($organization)
                ->withHttpClient(new GuzzleClient(['timeout' => $timeout]))
                ->make();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
