<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\Upload::create([
    'category' => 'Others',
    'file_path' => 'test_file.jpg',
    'original_filename' => 'test',
    'uploaded_by' => 1
]);

echo "UPLOAD ID IS: " . ($u->upload_id ?? 'NULL_OR_EMPTY') . "\n";
