<?php

namespace Paeire\LaravelSecretsManager;

use Aws\SecretsManager\SecretsManagerClient;

class SecretsManager
{
    protected SecretsManagerClient $client;

    public function __construct()
    {
        $this->client = new SecretsManagerClient([
            'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
            'version' => 'latest',
        ]);
    }

    public function loadSecretsIntoConfig(string $secretId): void
    {
        try {
            $secretId = getenv('AWS_SECRET_ID') ?: 'local/dev/env';
            $result = $this->client->getSecretValue([
                'SecretId' => $secretId,
            ]);

            $secrets = json_decode($result['SecretString'], true);

            //error_log('[SecretsManager] Secrets loaded: ' . json_encode($secrets)); // 👈 Add this

            foreach ($secrets as $key => $value) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                config()->set("secrets.$key", $value);
            }
        } catch (\Exception $e) {
            error_log("[SecretsManager] Failed to load secrets: " . $e->getMessage());        
        }
    }

}
