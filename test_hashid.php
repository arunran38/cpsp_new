<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$petition = App\Models\Petition::first();
if ($petition) {
    echo "Petition ID: " . $petition->petition_id . PHP_EOL;
    echo "Hashid: " . $petition->hashid . PHP_EOL;
    echo "Route show: " . route('petitions.show', $petition) . PHP_EOL;
    echo "Route edit: " . route('petitions.edit', $petition) . PHP_EOL;
    $decoded = App\Services\HashidService::decode($petition->hashid);
    echo "Decoded back to: " . $decoded . PHP_EOL;

    $foundByHash = (new App\Models\Petition)->resolveRouteBinding($petition->hashid);
    echo "Found via resolveRouteBinding(hashid): " . ($foundByHash ? $foundByHash->petition_id : 'FAILED') . PHP_EOL;

    $foundByNumeric = (new App\Models\Petition)->resolveRouteBinding($petition->petition_id);
    echo "Found via resolveRouteBinding(numeric): " . ($foundByNumeric ? $foundByNumeric->petition_id : 'FAILED') . PHP_EOL;
}
