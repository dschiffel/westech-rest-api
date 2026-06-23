<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application;
use Symfony\Component\HttpFoundation\Request;
use Dotenv\Dotenv;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$request = Request::createFromGlobals();

// Support CLI migration
if (PHP_SAPI === 'cli') {
    $argv = $_SERVER['argv'] ?? [];
    foreach ($argv as $arg) {
        if ($arg === 'migrate=1' || $arg === '--migrate') {
            $request->query->set('migrate', '1');
            break;
        }
    }
    
    // If not migration, we don't support other CLI actions yet
    if (!$request->query->has('migrate')) {
        echo "Usage: php public/index.php migrate=1\n";
        exit(0);
    }
}

$app = new Application();

// Run migrations if requested (simple way for this assignment)
if ($request->query->has('migrate')) {
    try {
        $app->runMigrations();
        echo "Migrations executed successfully.";
        exit;
    } catch (\Throwable $e) {
        echo "Migration error: " . $e->getMessage();
        exit(1);
    }
}

$response = $app->handle($request);
$response->send();
