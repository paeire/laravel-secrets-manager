<?php

namespace Paeire\LaravelSecretsManager;

use Illuminate\Support\ServiceProvider;

class SecretsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/secrets.php',
            'secrets'
        );

        // Bind singleton
        $this->app->singleton(SecretsManager::class, function () {
            return new SecretsManager();
        });
    }

    public function boot(): void
    {
        // Allow config publishing
        $this->publishes([
            __DIR__ . '/../config/secrets.php' => config_path('secrets.php'),
        ], 'config');

        // Load secrets
        $secretId = config('secrets.secret_id');
        app(SecretsManager::class)->loadSecretsIntoConfig($secretId);
    }
}
