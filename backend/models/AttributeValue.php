<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

class AttributeValue {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $sql = "INSERT INTO attribute_values (attribute_id, value, slug) VALUES (:attribute_id, :value, :slug)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':attribute_id', $data['attribute_id']);
        $stmt->bindParam(':value', $data['value']);
        $stmt->bindParam(':slug', $data['slug']);
        
        if ($stmt->execute()) {
            return $this->getById($this->conn->lastInsertId());
        }
        return false;
    }

    public function getByAttribute($attribute_id) {
        $sql = "SELECT av.*, a.name as attribute_name FROM attribute_values av 
                JOIN attributes a ON av.attribute_id = a.id 
                WHERE av.attribute_id = :attribute_id 
                ORDER BY av.value ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':attribute_id', $attribute_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT av.*, a.name as attribute_name FROM attribute_values av 
                JOIN attributes a ON av.attribute_id = a.id 
                WHERE av.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $sql = "UPDATE attribute_values SET value = :value, slug = :slug WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':value', $data['value']);
        $stmt->bindParam(':slug', $data['slug']);
        
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM attribute_values WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}