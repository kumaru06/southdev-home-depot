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
        if ($this->getProductCount($id) > 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE categories SET is_active = 0 WHERE id = ? AND is_active = 1");
        $stmt->execute([(int) $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Soft-delete only empty categories.
     * @return array{deleted:int,blocked:array<int,array{id:int,name:string,product_count:int}>}
     */
    public function deleteMany(array $ids) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        $result = ['deleted' => 0, 'blocked' => []];
        if (empty($ids)) {
            return $result;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT c.id, c.name,
                    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
             FROM categories c
             WHERE c.id IN ($placeholders) AND c.is_active = 1"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        $emptyIds = [];
        foreach ($rows as $row) {
            $count = (int) ($row['product_count'] ?? 0);
            if ($count > 0) {
                $result['blocked'][] = [
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'product_count' => $count,
                ];
            } else {
                $emptyIds[] = (int) $row['id'];
            }
        }

        if (!empty($emptyIds)) {
            $delPlaceholders = implode(',', array_fill(0, count($emptyIds), '?'));
            $delStmt = $this->pdo->prepare(
                "UPDATE categories SET is_active = 0 WHERE id IN ($delPlaceholders) AND is_active = 1"
            );
            $delStmt->execute($emptyIds);
            $result['deleted'] = $delStmt->rowCount();
        }

        return $result;
    }

    public function getProductCount($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
