<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $response = Illuminate\Support\Facades\Http::acceptJson()->timeout(10)->withoutVerifying()->get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com');
    echo 'status=' . $response->status() . PHP_EOL;
    echo 'ok=' . ($response->successful() ? 'yes' : 'no') . PHP_EOL;
    echo substr($response->body(), 0, 200) . PHP_EOL;
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
}
