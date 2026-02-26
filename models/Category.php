<?php
/**
 * Category Model
 */

class Category {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll() {
        return $this->pdo->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) as product_count
            FROM categories c 
            WHERE c.is_active = 1 
            ORDER BY c.name
        ")->fetchAll();
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
        return $stmt->execute([$data['name'], $data['description'] ?? null, $data['image'] ?? null]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, description = ?, image = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['description'], $data['image'] ?? null, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE categories SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getProductCount($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }
}
