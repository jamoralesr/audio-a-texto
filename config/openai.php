<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and Organization.
    | This will be used to authenticate with the OpenAI API.
    |
    */

    'api_key' => env('OPENAI_API_KEY', ''),
    'organization' => env('OPENAI_ORGANIZATION', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Transcription Model
    |--------------------------------------------------------------------------
    |
    | This option controls the default transcription model that will be used for
    | audio transcription. You can change this to any of the supported models.
    |
    | Supported models: whisper-1
    |
    */

    'transcription_model' => env('OPENAI_TRANSCRIPTION_MODEL', 'whisper-1'),

    /*
    |--------------------------------------------------------------------------
    | Default Transcription Language
    |--------------------------------------------------------------------------
    |
    | This option controls the default language that will be used for
    | transcription. You can change this to any of the supported languages.
    |
    */

    'default_language' => env('OPENAI_DEFAULT_LANGUAGE', 'es'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | This option controls the timeout for API requests to OpenAI.
    | Increase this if you're transcribing longer audio files.
    |
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 120),
];
