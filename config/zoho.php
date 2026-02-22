<?php

return [
    'client_id'      => env('ZOHO_CLIENT_ID'),
    'client_secret'  => env('ZOHO_CLIENT_SECRET'),
    'refresh_token'  => env('ZOHO_REFRESH_TOKEN'),
    'dc'             => env('ZOHO_DC', 'com'),
    'accounts_base'  => env('ZOHO_ACCOUNTS_BASE', 'https://accounts.zoho.com'),
    'api_base'       => env('ZOHO_API_BASE', 'https://www.zohoapis.com'),
    
    // Zoho Creator Config
    'creator_owner_name'    => env('ZOHO_CREATOR_OWNER_NAME', 'zoho_ali979'),
    'creator_app_link_name' => env('ZOHO_CREATOR_APP_LINK_NAME', 'object-system'),
    'creator_api_base'      => env('ZOHO_CREATOR_API_BASE', 'https://creator.zoho.com/api/v2'),
    
    // Public Publish Hashes (to bypass login)
    'creator_quotes_hash'   => env('ZOHO_CREATOR_QUOTES_HASH'),
    'creator_contracts_hash' => env('ZOHO_CREATOR_CONTRACTS_HASH'),
    
    // Scopes required: ZohoCRM.modules.quotes.READ, ZohoCRM.modules.accounts.READ, ZohoCRM.modules.contacts.READ, ZohoCreator.report.READ
];
