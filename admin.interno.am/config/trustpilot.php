<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trustpilot API Configuration
    |--------------------------------------------------------------------------
    */

    'redirect_uri' => env('TRUSTPILOT_REDIRECT_URI'),
    'access_token_url' => env('TRUSTPILOT_ACCESS_TOKEN_URL'),
    'search_url' => env('TRUSTPILOT_SEARCH_URL'),
    'invitation_links_url' => env('TRUSTPILOT_INVITATION_LINKS_URL'),
    'product_reviews_links_url' => env('TRUSTPILOT_PRODUCT_REVIEWS_LINKS_URL'),
];
