<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Resend API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key for Resend. You can find this in your Resend
    | dashboard at https://resend.com/api-keys
    |
    */
    'api_key' => env('RESEND_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default From Address
    |--------------------------------------------------------------------------
    |
    | This is the default from address for emails sent by Resend.
    |
    */
    'from' => [
        'address' => env('RESEND_FROM_ADDRESS', 'no-reply@eltranscriptor.com'),
        'name' => env('RESEND_FROM_NAME', 'El Transcriptor'),
    ],
];
