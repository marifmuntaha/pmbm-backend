<?php

return [
    'url' => env('WHATSAPP_SERVICE_URL', 'http://localhost:3000'),
    'user' => env('WHATSAPP_SERVICE_USER', 'secret'),
    'pass' => env('WHATSAPP_SERVICE_PASS', 'secret'),
    'device_id' => env('WHATSAPP_DEVICE_ID', ''),
];
