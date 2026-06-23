<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Config\Config;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class BearerTokenMiddleware
{
    public function __construct(private Config $config)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            return new JsonResponse(['error' => 'Missing Authorization header'], Response::HTTP_UNAUTHORIZED);
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Invalid Authorization header format'], Response::HTTP_FORBIDDEN);
        }

        $token = substr($authHeader, 7);
        if ($token !== $this->config->getBearerToken()) {
            return new JsonResponse(['error' => 'Invalid token'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
