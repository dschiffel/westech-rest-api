<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\ProductService;
use App\Repository\ProductRepository;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private $repository;
    private $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepository::class);
        $this->service = new ProductService($this->repository);
    }

    public function testFormatProductCalculatesVatCorrectively(): void
    {
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'description' => 'Desc',
            'brand' => 'Brand',
            'category' => 'Cat',
            'price_without_vat' => 100.0,
            'vat_rate' => 20.0,
            'created_at' => '2024-01-01',
            'updated_at' => '2024-01-01',
        ];

        $this->repository->method('findById')->willReturn($productData);

        $result = $this->service->getProduct(1);

        $this->assertEquals(120.0, $result['price_with_vat']);
    }

    public function testFormatProductHandlesRounding(): void
    {
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'description' => 'Desc',
            'brand' => 'Brand',
            'category' => 'Cat',
            'price_without_vat' => 10.123,
            'vat_rate' => 21.0,
            'created_at' => '2024-01-01',
            'updated_at' => '2024-01-01',
        ];

        $this->repository->method('findById')->willReturn($productData);

        $result = $this->service->getProduct(1);

        // 10.123 * 1.21 = 12.24883 -> 12.25
        $this->assertEquals(12.25, $result['price_with_vat']);
    }
}
