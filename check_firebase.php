<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (['firebase_enabled','firebase_api_key','firebase_auth_domain','firebase_project_id','firebase_app_id'] as $key) {
    $value = App\Models\SystemSetting::get($key, null);
    echo $key . '=' . var_export($value, true) . PHP_EOL;
}
