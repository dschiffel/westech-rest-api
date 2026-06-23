<?php

declare(strict_types=1);

namespace App\Command;

use PDO;
use RuntimeException;

readonly class MigrationCommand
{
    public function __construct(private PDO $pdo)
    {
    }

    public function migrate(): void
    {
        $migrationFile = __DIR__ . '/../../migrations/001_create_products.sql';
        if (!file_exists($migrationFile)) {
            throw new RuntimeException("Migration file not found: $migrationFile");
        }

        $sql = file_get_contents($migrationFile);
        $this->pdo->exec($sql);
    }
}
