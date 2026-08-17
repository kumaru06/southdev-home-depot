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
            $sql .= " ORDER BY p.created_at DESC";
        } else {
            // "All Products": group by category with Tiles (main product line) first
            $sql .= " ORDER BY CASE WHEN c.name = 'Tiles' THEN 0 ELSE 1 END, c.name ASC, p.name ASC";
        }

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
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (category_id, name, description, specifications, price, image, sku, size_label, size_group)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'],
            $data['specifications'] ?? null,
            $data['price'],
            $data['image'] ?? null,
            $data['sku'] ?? null,
            $data['size_label'] ?? null,
            $data['size_group'] ?? null,
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare(
            "UPDATE products
             SET category_id = ?, name = ?, description = ?, specifications = ?, price = ?, image = ?, sku = ?, size_label = ?, size_group = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'],
            $data['specifications'] ?? null,
            $data['price'],
            $data['image'],
            $data['sku'],
            $data['size_label'] ?? null,
            $data['size_group'] ?? null,
            $id,
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteMany(array $ids) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("UPDATE products SET is_active = 0 WHERE id IN ($placeholders) AND is_active = 1");
        $stmt->execute($ids);
        return $stmt->rowCount();
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

    /* ===== Gallery ===== */

    public function getImages($productId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([(int) $productId]);
        return $stmt->fetchAll();
    }

    public function getImagesByProductIds(array $productIds) {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));
        if (empty($productIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT * FROM product_images WHERE product_id IN ($placeholders) ORDER BY product_id ASC, sort_order ASC, id ASC"
        );
        $stmt->execute($productIds);
        $grouped = [];
        foreach ($stmt->fetchAll() as $row) {
            $grouped[(int) $row['product_id']][] = $row;
        }
        return $grouped;
    }

    public function addImage($productId, $filename, $sortOrder = null) {
        if ($sortOrder === null) {
            $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id = ?");
            $stmt->execute([(int) $productId]);
            $sortOrder = (int) $stmt->fetchColumn();
        }
        $stmt = $this->pdo->prepare(
            "INSERT INTO product_images (product_id, filename, sort_order) VALUES (?, ?, ?)"
        );
        $stmt->execute([(int) $productId, $filename, (int) $sortOrder]);
        return $this->pdo->lastInsertId();
    }

    public function deleteImage($imageId, $productId = null) {
        if ($productId !== null) {
            $stmt = $this->pdo->prepare("SELECT * FROM product_images WHERE id = ? AND product_id = ?");
            $stmt->execute([(int) $imageId, (int) $productId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM product_images WHERE id = ?");
            $stmt->execute([(int) $imageId]);
        }
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $del = $this->pdo->prepare("DELETE FROM product_images WHERE id = ?");
        $del->execute([(int) $row['id']]);
        return $row;
    }

    public function syncPrimaryImage($productId) {
        $images = $this->getImages($productId);
        $filename = !empty($images) ? $images[0]['filename'] : null;
        $stmt = $this->pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        $stmt->execute([$filename, (int) $productId]);
        return $filename;
    }

    /* ===== Specs / sizes / related ===== */

    public static function encodeSpecifications(array $pairs): ?string {
        $clean = [];
        foreach ($pairs as $key => $value) {
            $k = trim((string) $key);
            $v = trim((string) $value);
            if ($k === '' || $v === '') {
                continue;
            }
            $clean[$k] = $v;
        }
        if (empty($clean)) {
            return null;
        }
        return json_encode($clean, JSON_UNESCAPED_UNICODE);
    }

    public static function decodeSpecifications($raw): array {
        if ($raw === null || $raw === '') {
            return [];
        }
        if (is_array($raw)) {
            return $raw;
        }
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function getSizeSiblings($sizeGroup, $excludeId = null) {
        $sizeGroup = trim((string) $sizeGroup);
        if ($sizeGroup === '') {
            return [];
        }
        $sql = "SELECT p.id, p.name, p.price, p.size_label, p.size_group, p.image, i.quantity as stock
                FROM products p
                LEFT JOIN inventory i ON p.id = i.product_id
                WHERE p.is_active = 1 AND p.size_group = ?";
        $params = [$sizeGroup];
        if ($excludeId !== null) {
            // Include current product too for the size grid highlight — do not exclude
        }
        $sql .= " ORDER BY p.size_label ASC, p.price ASC, p.name ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRelated($categoryId, $excludeId, $limit = 8) {
        $limit = max(1, (int) $limit);
        $excludeId = (int) $excludeId;
        $categoryId = (int) $categoryId;

        $stmt = $this->pdo->prepare(
            "SELECT p.*, c.name as category_name, i.quantity as stock
             FROM products p
             JOIN categories c ON p.category_id = c.id
             LEFT JOIN inventory i ON p.id = i.product_id
             WHERE p.is_active = 1 AND p.category_id = ? AND p.id != ?
             ORDER BY p.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $related = $stmt->fetchAll();

        // Fallback: fill with other active products so the section still shows
        if (count($related) < $limit) {
            $haveIds = array_map('intval', array_column($related, 'id'));
            $haveIds[] = $excludeId;
            $placeholders = implode(',', array_fill(0, count($haveIds), '?'));
            $need = $limit - count($related);
            $sql = "SELECT p.*, c.name as category_name, i.quantity as stock
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    LEFT JOIN inventory i ON p.id = i.product_id
                    WHERE p.is_active = 1 AND p.id NOT IN ($placeholders)
                    ORDER BY p.created_at DESC
                    LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $i = 1;
            foreach ($haveIds as $hid) {
                $stmt->bindValue($i++, $hid, PDO::PARAM_INT);
            }
            $stmt->bindValue($i, $need, PDO::PARAM_INT);
            $stmt->execute();
            $related = array_merge($related, $stmt->fetchAll());
        }

        return $related;
    }

    /* ===== Size options (Choose Your Size) ===== */

    public function getSizeOptions($productId) {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM product_size_options WHERE product_id = ? ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([(int) $productId]);
        return $stmt->fetchAll();
    }

    public function findSizeOption($sizeOptionId, $productId = null) {
        if ($productId !== null) {
            $stmt = $this->pdo->prepare("SELECT * FROM product_size_options WHERE id = ? AND product_id = ?");
            $stmt->execute([(int) $sizeOptionId, (int) $productId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM product_size_options WHERE id = ?");
            $stmt->execute([(int) $sizeOptionId]);
        }
        return $stmt->fetch() ?: null;
    }

    public function replaceSizeOptions($productId, array $options): void {
        $del = $this->pdo->prepare("DELETE FROM product_size_options WHERE product_id = ?");
        $del->execute([(int) $productId]);

        $ins = $this->pdo->prepare(
            "INSERT INTO product_size_options (product_id, size_label, price, stock, sort_order)
             VALUES (?, ?, ?, ?, ?)"
        );
        $order = 0;
        foreach ($options as $opt) {
            $label = trim((string) ($opt['size_label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $ins->execute([
                (int) $productId,
                $label,
                (float) ($opt['price'] ?? 0),
                max(0, (int) ($opt['stock'] ?? 0)),
                $order++,
            ]);
        }
    }

    public function reserveSizeStock($sizeOptionId, $quantity): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE product_size_options
             SET stock = stock - ?
             WHERE id = ? AND stock >= ?"
        );
        $stmt->execute([(int) $quantity, (int) $sizeOptionId, (int) $quantity]);
        return $stmt->rowCount() > 0;
    }

    public function restoreSizeStock($sizeOptionId, $quantity): bool {
        $stmt = $this->pdo->prepare(
            "UPDATE product_size_options SET stock = stock + ? WHERE id = ?"
        );
        return $stmt->execute([(int) $quantity, (int) $sizeOptionId]);
    }
}
