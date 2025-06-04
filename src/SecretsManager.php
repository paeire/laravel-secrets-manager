<?php

namespace Paeire\LaravelSecretsManager;

use Aws\SecretsManager\SecretsManagerClient;

class SecretsManager
{
    protected SecretsManagerClient $client;

    public function __construct()
    {
        $this->client = new SecretsManagerClient([
            'region' => config('secrets.region'),
            'version' => 'latest',
        ]);
    }

    public function loadSecretsIntoConfig(string $secretId): void
    {
        try {
            $result = $this->client->getSecretValue([
                'SecretId' => $secretId,
            ]);

            $secrets = json_decode($result['SecretString'], true);

            foreach ($secrets as $key => $value) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                config()->set("secrets.$key", $value);
            }
        } catch (\Exception $e) {
            logger()->error("Failed to load secrets: " . $e->getMessage());
        }
    }
}
