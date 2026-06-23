<?php

declare(strict_types=1);

namespace App\Config;

class Config
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'app_env' => $_ENV['APP_ENV'] ?? 'dev',
            'app_debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'bearer_token' => $_ENV['APP_BEARER_TOKEN'] ?? 'westech-secret-token',
            'db' => [
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => (int)($_ENV['DB_PORT'] ?? 5432),
                'name' => $_ENV['DB_NAME'] ?? 'products_db',
                'user' => $_ENV['DB_USER'] ?? 'app_user',
                'password' => $_ENV['DB_PASSWORD'] ?? 'app_password',
            ],
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function getDbConfig(): array
    {
        return $this->config['db'];
    }

    public function getBearerToken(): string
    {
        return $this->config['bearer_token'];
    }
}
