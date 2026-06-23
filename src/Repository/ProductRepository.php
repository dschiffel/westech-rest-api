<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\ProductInput;
use PDO;

readonly class ProductRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(ProductInput $input): array
    {
        $sql = "INSERT INTO products (name, description, brand, category, price_without_vat, vat_rate)
                VALUES (:name, :description, :brand, :category, :price_without_vat, :vat_rate)
                RETURNING *";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $input->name,
            'description' => $input->description,
            'brand' => $input->brand,
            'category' => $input->category,
            'price_without_vat' => $input->priceWithoutVat,
            'vat_rate' => $input->vatRate,
        ]);

        return $stmt->fetch();
    }

    public function update(int $id, ProductInput $input): ?array
    {
        $sql = "UPDATE products 
                SET name = :name, 
                    description = :description, 
                    brand = :brand, 
                    category = :category, 
                    price_without_vat = :price_without_vat, 
                    vat_rate = :vat_rate,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
                RETURNING *";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'name' => $input->name,
            'description' => $input->description,
            'brand' => $input->brand,
            'category' => $input->category,
            'price_without_vat' => $input->priceWithoutVat,
            'vat_rate' => $input->vatRate,
        ]);

        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]) && $stmt->rowCount() > 0;
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByName(string $name, ?int $excludeId = null): ?array
    {
        $sql = "SELECT * FROM products WHERE name = :name";
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $params = ['name' => $name];
        if ($excludeId !== null) {
            $params['excludeId'] = $excludeId;
        }
        
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function list(int $page, int $limit, ?string $category = null, ?string $brand = null): array
    {
        $offset = ($page - 1) * $limit;
        $where = [];
        $params = [];

        if ($category) {
            $where[] = "category = :category";
            $params['category'] = $category;
        }

        if ($brand) {
            $where[] = "brand = :brand";
            $params['brand'] = $brand;
        }

        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

        // Get total count
        $countSql = "SELECT COUNT(*) FROM products $whereSql";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        // Get data
        $dataSql = "SELECT * FROM products $whereSql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($dataSql);
        
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
        ];
    }
}
