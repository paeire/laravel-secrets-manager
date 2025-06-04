<?php

namespace Paeire\LaravelSecretsManager;

use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\ServiceProvider;

class SecretsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Only run in production
        if ($this->app->environment(['local', 'production'])) {
            $this->loadSecrets();
        }
        $this->mergeConfigFrom(
            __DIR__ . '/../config/secrets.php',
            'secrets'
        );
    }

    protected function loadSecrets(): void
    {
        try {
            $client = new \Aws\SecretsManager\SecretsManagerClient([
                'region' => config('secrets.region', 'us-east-1'),
                'version' => 'latest',
            ]);

            $result = $client->getSecretValue([
                'SecretId' => config('secrets.secret_id'),
            ]);

            $secrets = json_decode($result['SecretString'], true);

            //error_log('[SecretsManager] Loaded: ' . json_encode($secrets));

            foreach ($secrets as $key => $value) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                config()->set("$key", $value);
            }   



        } catch (\Throwable $e) {
            error_log('[SecretsManager] Failed to load secrets: ' . $e->getMessage());
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/secrets.php' => config_path('secrets.php'),
        ], 'config');
    }
}
