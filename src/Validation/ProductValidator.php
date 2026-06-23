<?php

declare(strict_types=1);

namespace App\Validation;

use App\Repository\ProductRepository;

class ProductValidator
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function validate(array $data, ?int $id = null): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors['name'][] = 'Name is required';
        } elseif ($this->repository->findByName($data['name'], $id)) {
            $errors['name'][] = 'Name must be unique';
        }

        if (empty($data['brand'])) {
            $errors['brand'][] = 'Brand is required';
        }

        if (empty($data['category'])) {
            $errors['category'][] = 'Category is required';
        }

        if (!isset($data['price_without_vat'])) {
            $errors['price_without_vat'][] = 'Price without VAT is required';
        } elseif (!is_numeric($data['price_without_vat'])) {
            $errors['price_without_vat'][] = 'Price without VAT must be numeric';
        } elseif ((float)$data['price_without_vat'] <= 0) {
            $errors['price_without_vat'][] = 'Price without VAT must be greater than 0';
        }

        if (!isset($data['vat_rate'])) {
            $errors['vat_rate'][] = 'VAT rate is required';
        } elseif (!is_numeric($data['vat_rate'])) {
            $errors['vat_rate'][] = 'VAT rate must be numeric';
        } elseif ((float)$data['vat_rate'] < 0) {
            $errors['vat_rate'][] = 'VAT rate must be greater than or equal to 0';
        }

        return $errors;
    }
}
