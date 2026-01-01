<?php

return [
    'public_key' => env('FEDAPAY_PUBLIC_KEY'),
    'secret_key' => env('FEDAPAY_CLE_SECRET'),
    'environment' => env('FEDAPAY_ENVIRONNEMENT', 'sandbox'),
];
