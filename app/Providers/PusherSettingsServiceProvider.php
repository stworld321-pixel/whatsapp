<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;

class PusherSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            try {
                $key     = SystemSetting::get('pusher_app_key');
                $secret  = SystemSetting::get('pusher_app_secret');
                $appId   = SystemSetting::get('pusher_app_id');
                $cluster = SystemSetting::get('pusher_app_cluster');
                $enabled = SystemSetting::get('pusher_enabled', 'false');

                if ($key && $secret && $appId) {
                    config([
                        'broadcasting.default' => 'pusher',
                        'broadcasting.connections.pusher.key' => $key,
                        'broadcasting.connections.pusher.secret' => $secret,
                        'broadcasting.connections.pusher.app_id' => $appId,
                        'broadcasting.connections.pusher.options.cluster' => $cluster ?: 'mt1',
                        'broadcasting.connections.pusher.options.host' => 'api-'.($cluster ?: 'mt1').'.pusher.com',
                        'broadcasting.connections.pusher.client_options' => $this->pusherClientOptions(),
                    ]);
                }
            } catch (\Throwable) {
                // DB not ready during migrations — skip silently
            }
        });
    }

    private function pusherClientOptions(): array
    {
        $verify = $this->pusherVerifyOption();

        return [
            'verify' => $verify,
        ];
    }

    private function pusherVerifyOption(): bool|string
    {
        $appUrl = config('app.url', '');

        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            return false;
        }

        foreach ([
            'C:\\xamp\\php\\extras\\ssl\\cacert.pem',
            'C:\\xampp\\php\\extras\\ssl\\cacert.pem',
        ] as $caBundlePath) {
            if (is_file($caBundlePath)) {
                return $caBundlePath;
            }
        }

        return true;
    }
}
