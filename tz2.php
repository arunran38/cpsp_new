<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = Illuminate\Http\Request::create(
    '/profile', 'PATCH',
    [
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'mobile_number' => '1234567890',
        'designation' => 'Admin'
    ],
    [],
    [
        'photo' => new \Illuminate\Http\UploadedFile(
            __DIR__ . '/tz.php',
            'tz.php',
            'text/php',
            null,
            true // test mode
        )
    ]
);
$app->instance('request', $request);

$user = App\Models\User::first();

$formRequest = App\Http\Requests\ProfileUpdateRequest::createFrom($request);
$formRequest->setContainer($app)->setRedirector($app->make(\Illuminate\Routing\Redirector::class));
$validator = validator($formRequest->all(), $formRequest->rules());
$validated = $validator->validated();

var_dump(array_keys($validated));

$safe = new \Illuminate\Support\ValidatedInput($validated);
$filled = $safe->except(['photo']);
echo "Filled array keys:\n";
var_dump(array_keys($filled));

$user->fill($filled);
echo "User is dirty: " . ($user->isDirty() ? 'YES' : 'NO') . "\n";

if ($request->hasFile('photo')) {
    echo "Has file photo!\n";
    $file = $request->file('photo');
    if ($file->isValid()) {
         echo "File is valid!\n";
    } else {
         echo "File NOT valid! Error: " . $file->getError() . "\n";
    }
} else {
    echo "NO PHOTO FILE FOUND IN REQUEST\n";
}
