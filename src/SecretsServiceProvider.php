<?php

namespace Paeire\LaravelSecretsManager;

use Aws\SecretsManager\SecretsManagerClient;
use Illuminate\Support\ServiceProvider;

class SecretsServiceProvider extends ServiceProvider
{
    public function register(): void
    {   
        if (config('secrets.full_config')) {
            $this->loadSecrets();
        }
        if(config('secrets.rds_rotation'))
        {
            $this->rdsWRotation();

        }
        if($this->app)
        $this->mergeConfigFrom(
            __DIR__ . '/../config/secrets.php',
            'secrets'
        );
    }

    protected function rdsWRotation(): void
    {
        try {
            $tagsArray = array(
                'username' => 'database.connections.mysql.username',
                'password' => 'database.connections.mysql.password',
                'engine' => 'engine',
                'host' => 'database.connections.mysql.host',
                'port' => 'port',
                'dbClusterIdentifier' => 'dbClusterIdentifier',
            );
            $client = new \Aws\SecretsManager\SecretsManagerClient([
                'region' => config('secrets.region', 'us-east-1'),
                'version' => 'latest',
            ]);

            $result = $client->getSecretValue([
                'SecretId' => config('secrets.rds_secret_id'),
            ]);

            $secrets = json_decode($result['SecretString'], true);

            error_log('[SecretsManager] Loaded: ' . json_encode($secrets));
            

            foreach ($secrets as $key => $value) {
                $envKey = $tagsArray[$key];
                putenv("$envKey=$value");
                $_ENV[$envKey] = $value;
                $_SERVER[$envKey] = $value;
                config()->set("$envKey", $value);
            }   

            if(config('secrets.rds_read_write'))
            {
                $read = 'database.connections.mysql.read.host';
                $write = 'database.connections.mysql.write.host';
                $host = config('database.connections.mysql.host');
                putenv("$read=$host");
                $_ENV[$read] = $host;
                $_SERVER[$read] = $host;
                config()->set("$read", $host);

                putenv("$write=$host");
                $_ENV[$write] = $host;
                $_SERVER[$write] = $host;
                config()->set("$write", $host);
            }


        } catch (\Throwable $e) {
            error_log('[SecretsManager] Failed to load rds secrets: ' . $e->getMessage());
        }
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
