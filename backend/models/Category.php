<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

class Category {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $sql = "INSERT INTO categories (name, slug, description, image_url, parent_id, level, is_active) 
                VALUES (:name, :slug, :description, :image_url, :parent_id, :level, :is_active)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':parent_id', $data['parent_id']);
        $stmt->bindParam(':level', $data['level']);
        $stmt->bindParam(':is_active', $data['is_active']);
        
        if ($stmt->execute()) {
            return $this->getById($this->conn->lastInsertId());
        }
        return false;
    }

    public function getAll($active_only = true) {
        $sql = "SELECT * FROM categories";
        if ($active_only) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY level ASC, name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getChildren($parent_id) {
        $sql = "SELECT * FROM categories WHERE parent_id = :parent_id AND is_active = 1 ORDER BY name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $sql = "UPDATE categories SET 
                name = :name, 
                slug = :slug, 
                description = :description, 
                image_url = :image_url, 
                parent_id = :parent_id, 
                level = :level, 
                is_active = :is_active 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':image_url', $data['image_url']);
        $stmt->bindParam(':parent_id', $data['parent_id']);
        $stmt->bindParam(':level', $data['level']);
        $stmt->bindParam(':is_active', $data['is_active']);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    private function generateSlug($name) {
        $slug = strtolower($name);
        $slug = str_replace(' ', '-', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        return $slug;
    }
}