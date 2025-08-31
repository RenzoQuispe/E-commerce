<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

class Attributes {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $sql = "INSERT INTO attributes (name, slug, is_variation) VALUES (:name, :slug, :is_variation)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':is_variation', $data['is_variation']);
        
        if ($stmt->execute()) {
            return $this->getById($this->conn->lastInsertId());
        }
        return false;
    }

    public function getAll($variation_only = false) {
        $sql = "SELECT * FROM attributes";
        if ($variation_only) {
            $sql .= " WHERE is_variation = 1";
        }
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM attributes WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getWithValues($id) {
        $attribute = $this->getById($id);
        if ($attribute) {
            $sql = "SELECT * FROM attribute_values WHERE attribute_id = :attribute_id ORDER BY value ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':attribute_id', $id);
            $stmt->execute();
            
            $attribute['values'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $attribute;
    }

    public function update($id, $data) {
        $sql = "UPDATE attributes SET name = :name, slug = :slug, is_variation = :is_variation WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':slug', $data['slug']);
        $stmt->bindParam(':is_variation', $data['is_variation']);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM attributes WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}