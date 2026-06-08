<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Outlook API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for Outlook API integration.
    | You can define the client ID, client secret, tenant ID, and other
    | settings required to authenticate and interact with Microsoft Graph API.
    |
    */

    'client_id' => env('OUTLOOK_CLIENT_ID'),
    'object_id' => env('OUTLOOK_OBJECT_ID'),
    'client_secret' => env('OUTLOOK_CLIENT_SECRET'),
    'tenant_id' => env('OUTLOOK_TENANT_ID'),
    'redirect_uri' => env('OUTLOOK_REDIRECT_URI'),
    'scope' => env('OUTLOOK_SCOPE'),
    'scope_admin' => env('OUTLOOK_SCOPE_ADMIN'),

];
