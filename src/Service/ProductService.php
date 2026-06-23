<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ProductInput;
use App\Repository\ProductRepository;

class ProductService
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function createProduct(ProductInput $input): array
    {
        $product = $this->repository->create($input);
        return $this->formatProduct($product);
    }

    public function updateProduct(int $id, ProductInput $input): ?array
    {
        $product = $this->repository->update($id, $input);
        return $product ? $this->formatProduct($product) : null;
    }

    public function deleteProduct(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getProduct(int $id): ?array
    {
        $product = $this->repository->findById($id);
        return $product ? $this->formatProduct($product) : null;
    }

    public function listProducts(int $page, int $limit, ?string $category = null, ?string $brand = null): array
    {
        $result = $this->repository->list($page, $limit, $category, $brand);
        
        $data = array_map(fn($item) => $this->formatProduct($item), $result['data']);
        
        return [
            'data' => $data,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $result['total'],
                'total_pages' => ceil($result['total'] / $limit),
            ]
        ];
    }

    private function formatProduct(array $product): array
    {
        $priceWithoutVat = (float)$product['price_without_vat'];
        $vatRate = (float)$product['vat_rate'];
        $priceWithVat = round($priceWithoutVat * (1 + $vatRate / 100), 2);

        return [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'brand' => $product['brand'],
            'category' => $product['category'],
            'price_without_vat' => $priceWithoutVat,
            'vat_rate' => $vatRate,
            'price_with_vat' => $priceWithVat,
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at'],
        ];
    }
}
