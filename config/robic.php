<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bank Transfer Details (Manual Deposits)
    |--------------------------------------------------------------------------
    | Used on the user deposit page to show the account details the user should
    | transfer to. Update values via .env or directly here.
    */
    'bank_transfer' => [
        'bank_name'       => env('ROBIC_BANK_NAME', 'ROBIC Bank'),
        'account_name'    => env('ROBIC_BANK_ACCOUNT_NAME', 'ROBIC Platform'),
        'account_number'  => env('ROBIC_BANK_ACCOUNT_NUMBER', '000000000000'),
        'iban'            => env('ROBIC_BANK_IBAN', 'SA00 0000 0000 0000 0000 0000'),
        'swift'           => env('ROBIC_BANK_SWIFT', 'ROBICSA00'),
        'currency'        => env('ROBIC_BANK_CURRENCY', 'SAR'),
        'reference_hint'  => env('ROBIC_BANK_REFERENCE_HINT', 'Put your username in the transfer note'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Deposit Instructions (User Facing)
    |--------------------------------------------------------------------------
    */
    'deposit_instructions' => [
        'Transfer the amount to the account details below.',
        'Write your username (or your deposit TRX) in the transfer note if possible.',
        'Upload a clear receipt image after the transfer.',
        'Your wallet will be credited after admin approval.',
    ],
];


