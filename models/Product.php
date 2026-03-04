<?php
/**
 * Product Model
 */

class Product {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name, i.quantity as stock FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN inventory i ON p.id = i.product_id WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll($categoryId = null, $limit = null, $offset = 0) {
        $sql = "SELECT p.*, c.name as category_name, i.quantity as stock FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN inventory i ON p.id = i.product_id WHERE p.is_active = 1";

        $params = [];
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " ORDER BY p.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function skuExists($sku, $excludeId = null) {
        if (empty($sku)) return false;
        $sql = "SELECT COUNT(*) FROM products WHERE sku = ?";
        $params = [$sku];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO products (category_id, name, description, price, image, sku) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['category_id'], $data['name'], $data['description'],
            $data['price'], $data['image'] ?? null, $data['sku'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, image = ?, sku = ? WHERE id = ?");
        return $stmt->execute([
            $data['category_id'], $data['name'], $data['description'],
            $data['price'], $data['image'], $data['sku'], $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search($keyword) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 AND (p.name LIKE ? OR p.description LIKE ?)");
        $search = "%{$keyword}%";
        $stmt->execute([$search, $search]);
        return $stmt->fetchAll();
    }

    public function count($categoryId = null) {
        $sql = "SELECT COUNT(*) FROM products WHERE is_active = 1";
        $params = [];
        if ($categoryId) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
