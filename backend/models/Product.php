<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

class Product {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $sql = "INSERT INTO products (
            parent_id, product_type, name, slug, description,
            category_id, brand, model, sku, barcode, price, compare_price, cost_price,
            stock_quantity, manage_stock, stock_status, is_active, is_featured,
            featured_image, image_gallery
        ) VALUES (
            :parent_id, :product_type, :name, :slug, :description,
            :category_id, :brand, :model, :sku, :barcode, :price, :compare_price, :cost_price,
            :stock_quantity, :manage_stock, :stock_status, :is_active, :is_featured,
            :featured_image, :image_gallery
        )";
        
        $stmt = $this->conn->prepare($sql);
        
        // asociar parametros
        $stmt->bindParam(':parent_id', $data['parent_id']);
        $stmt->bindParam(':product_type', $data['product_type']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':sku', $data['sku']);
        $stmt->bindParam(':barcode', $data['barcode']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':compare_price', $data['compare_price']);
        $stmt->bindParam(':cost_price', $data['cost_price']);
        $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
        $stmt->bindParam(':manage_stock', $data['manage_stock']);
        $stmt->bindParam(':stock_status', $data['stock_status']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        $stmt->bindParam(':featured_image', $data['featured_image']);
        $stmt->bindParam(':image_gallery', $data['image_gallery']);
        
        if ($stmt->execute()) {
            return $this->getById($this->conn->lastInsertId());
        }
        return false;
    }

    public function getAll($filters = []) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['active_only']) && $filters['active_only']) {
            $sql .= " AND p.is_active = 1";
        }
        
        if (isset($filters['product_type'])) {
            $sql .= " AND p.product_type = :product_type";
            $params['product_type'] = $filters['product_type'];
        }
        
        if (isset($filters['category_id'])) {
            $sql .= " AND p.category_id = :category_id";
            $params['category_id'] = $filters['category_id'];
        }
        
        if (isset($filters['parent_id'])) {
            $sql .= " AND p.parent_id = :parent_id";
            $params['parent_id'] = $filters['parent_id'];
        }
        
        if (isset($filters['featured'])) {
            $sql .= " AND p.is_featured = :featured";
            $params['featured'] = $filters['featured'];
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
            if (isset($filters['offset'])) {
                $sql .= " OFFSET " . intval($filters['offset']);
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindParam(':' . $key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Decodificar image_gallery JSON
            if ($product['image_gallery']) {
                $product['image_gallery'] = json_decode($product['image_gallery'], true);
            }
            
            // Si es un producto padre, obtener sus variantes
            if ($product['product_type'] == 'parent') {
                $product['variants'] = $this->getVariants($id);
            }
            
            // Si es una variante, obtener sus atributos
            if ($product['product_type'] == 'variant') {
                $product['attributes'] = $this->getProductAttributes($id);
            }
        }
        
        return $product;
    }

    public function getVariants($parent_id) {
        $sql = "SELECT * FROM products WHERE parent_id = :parent_id AND is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->execute();
        
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($variants as &$variant) {
            if ($variant['image_gallery']) {
                $variant['image_gallery'] = json_decode($variant['image_gallery'], true);
            }
            $variant['attributes'] = $this->getProductAttributes($variant['id']);
        }
        
        return $variants;
    }

    public function getProductAttributes($product_id) {
        $sql = "SELECT 
            pa.id,
            a.name as attribute_name,
            a.slug as attribute_slug,
            av.value as attribute_value,
            av.slug as value_slug
        FROM product_attributes pa
        JOIN attributes a ON pa.attribute_id = a.id
        JOIN attribute_values av ON pa.attribute_value_id = av.id
        WHERE pa.product_id = :product_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $sql = "UPDATE products SET 
                name = :name, slug = :slug, description = :description, 
                category_id = :category_id,
                brand = :brand, model = :model, sku = :sku, barcode = :barcode,
                price = :price, compare_price = :compare_price, cost_price = :cost_price,
                stock_quantity = :stock_quantity, manage_stock = :manage_stock,
                stock_status = :stock_status, is_active = :is_active,
                is_featured = :is_featured, featured_image = :featured_image,
                image_gallery = :image_gallery
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':brand', $data['brand']);
        $stmt->bindParam(':model', $data['model']);
        $stmt->bindParam(':sku', $data['sku']);
        $stmt->bindParam(':barcode', $data['barcode']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':compare_price', $data['compare_price']);
        $stmt->bindParam(':cost_price', $data['cost_price']);
        $stmt->bindParam(':stock_quantity', $data['stock_quantity']);
        $stmt->bindParam(':manage_stock', $data['manage_stock']);
        $stmt->bindParam(':stock_status', $data['stock_status']);
        $stmt->bindParam(':is_active', $data['is_active']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        $stmt->bindParam(':featured_image', $data['featured_image']);
        $stmt->bindParam(':image_gallery', $data['image_gallery']);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function updateStock($id, $quantity) {
        $sql = "UPDATE products SET stock_quantity = :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':quantity', $quantity);
        
        return $stmt->execute();
    }
}