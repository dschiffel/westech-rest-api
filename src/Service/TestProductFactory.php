<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ProductInput;
use App\Repository\ProductRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class TestProductFactory
{
    public function __construct(
        private ProductRepository   $repository,
        private HttpClientInterface $httpClient
    ) {
    }

    public function createFromLocal(): array
    {
        $uniqueSuffix = bin2hex(random_bytes(3));
        $input = new ProductInput(
            name: 'Local Test Product ' . $uniqueSuffix,
            description: 'Generated from local dataset',
            brand: 'LocalBrand',
            category: 'test',
            priceWithoutVat: 100.0,
            vatRate: 20.0
        );

        return $this->repository->create($input);
    }

    public function createFromRemote(): array
    {
        $response = $this->httpClient->request('GET', 'https://dummyjson.com/products/1');
        $remoteData = $response->toArray();

        $uniqueSuffix = bin2hex(random_bytes(3));
        $input = new ProductInput(
            name: $remoteData['title'] . ' ' . $uniqueSuffix,
            description: $remoteData['description'] ?? null,
            brand: $remoteData['brand'] ?? 'Unknown',
            category: $remoteData['category'] ?? 'Unknown',
            priceWithoutVat: (float)$remoteData['price'],
            vatRate: 20.0
        );

        return $this->repository->create($input);
    }
}
