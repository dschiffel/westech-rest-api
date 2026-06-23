<?php

declare(strict_types=1);

namespace App\DTO;

readonly class ProductInput
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $brand,
        public string $category,
        public float $priceWithoutVat,
        public float $vatRate
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            brand: $data['brand'] ?? '',
            category: $data['category'] ?? '',
            priceWithoutVat: isset($data['price_without_vat']) ? (float)$data['price_without_vat'] : 0.0,
            vatRate: isset($data['vat_rate']) ? (float)$data['vat_rate'] : 0.0
        ) ;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'category' => $this->category,
            'price_without_vat' => $this->priceWithoutVat,
            'vat_rate' => $this->vatRate,
        ];
    }
}
