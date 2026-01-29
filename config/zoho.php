<?php

return [
    'client_id'      => env('ZOHO_CLIENT_ID'),
    'client_secret'  => env('ZOHO_CLIENT_SECRET'),
    'refresh_token'  => env('ZOHO_REFRESH_TOKEN'),
    'dc'             => env('ZOHO_DC', 'com'),
    'accounts_base'  => env('ZOHO_ACCOUNTS_BASE', 'https://accounts.zoho.com'),
    'api_base'       => env('ZOHO_API_BASE', 'https://www.zohoapis.com'),
    
    // Scopes required: ZohoCRM.modules.quotes.READ, ZohoCRM.modules.accounts.READ, ZohoCRM.modules.contacts.READ
];
