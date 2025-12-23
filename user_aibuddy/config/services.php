<?php
// config/services.php
$env = require __DIR__ . '/env.php';

return [
    'gemini' => [
        'api_key' => $env['GEMINI_API_KEY'],
        'model' => 'gemini-2.5-flash',
        'timeout' => 30
    ]
];
?>