<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = Illuminate\Http\Request::create(
    '/profile', 'PATCH',
    [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
    ],
    [],
    [
        'photo' => new \Illuminate\Http\UploadedFile(
            __DIR__ . '/tz.php',
            'tz.php',
            'text/php',
            UPLOAD_ERR_INI_SIZE, // Simulate file too large
            true
        )
    ]
);
$app->instance('request', $request);

$rules = ['photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']];
$validator = validator($request->all(), $rules);

if ($validator->fails()) {
    echo "VALIDATION FAILED:\n";
    print_r($validator->errors()->all());
} else {
    echo "VALIDATION PASSED!\n";
}
