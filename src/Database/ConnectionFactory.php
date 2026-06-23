<?php

declare(strict_types=1);

namespace App\Database;

use App\Config\Config;
use PDO;

class ConnectionFactory
{
    private ?PDO $pdo = null;

    public function __construct(private readonly Config $config)
    {
    }

    public function create(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $dbConfig = $this->config->getDbConfig();
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['name']
        );

        $this->pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $this->pdo;
    }
}
