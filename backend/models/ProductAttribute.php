<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

class ProductAttribute {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function assignAttribute($data) {
        // Verificar si ya existe la combinación
        $checkSql = "SELECT id FROM product_attributes WHERE product_id = :product_id AND attribute_id = :attribute_id";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bindParam(':product_id', $data['product_id']);
        $checkStmt->bindParam(':attribute_id', $data['attribute_id']);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            throw new Exception('El producto ya tiene asignado este atributo');
        }
        
        $sql = "INSERT INTO product_attributes (product_id, attribute_id, attribute_value_id) 
                VALUES (:product_id, :attribute_id, :attribute_value_id)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $data['product_id']);
        $stmt->bindParam(':attribute_id', $data['attribute_id']);
        $stmt->bindParam(':attribute_value_id', $data['attribute_value_id']);
        
        if ($stmt->execute()) {
            return $this->getById($this->conn->lastInsertId());
        }
        return false;
    }

    public function assignMultipleAttributes($product_id, $attributes) {
        $this->conn->beginTransaction();
        
        try {
            // Primero eliminar atributos existentes del producto
            $deleteSql = "DELETE FROM product_attributes WHERE product_id = :product_id";
            $deleteStmt = $this->conn->prepare($deleteSql);
            $deleteStmt->bindParam(':product_id', $product_id);
            $deleteStmt->execute();
            
            // Insertar los nuevos atributos
            foreach ($attributes as $attribute) {
                $data = [
                    'product_id' => $product_id,
                    'attribute_id' => $attribute['attribute_id'],
                    'attribute_value_id' => $attribute['attribute_value_id']
                ];
                
                if (!$this->assignAttribute($data)) {
                    throw new Exception('Error al asignar atributo');
                }
            }
            
            $this->conn->commit();
            return $this->getByProduct($product_id);
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getByProduct($product_id) {
        $sql = "SELECT 
            pa.id,
            pa.product_id,
            a.id as attribute_id,
            a.name as attribute_name,
            a.slug as attribute_slug,
            av.id as attribute_value_id,
            av.value as attribute_value,
            av.slug as value_slug
        FROM product_attributes pa
        JOIN attributes a ON pa.attribute_id = a.id
        JOIN attribute_values av ON pa.attribute_value_id = av.id
        WHERE pa.product_id = :product_id
        ORDER BY a.name ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT 
            pa.id,
            pa.product_id,
            p.name as product_name,
            a.id as attribute_id,
            a.name as attribute_name,
            a.slug as attribute_slug,
            av.id as attribute_value_id,
            av.value as attribute_value,
            av.slug as value_slug
        FROM product_attributes pa
        JOIN products p ON pa.product_id = p.id
        JOIN attributes a ON pa.attribute_id = a.id
        JOIN attribute_values av ON pa.attribute_value_id = av.id
        WHERE pa.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateAttribute($id, $data) {
        $sql = "UPDATE product_attributes SET 
                attribute_value_id = :attribute_value_id 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':attribute_value_id', $data['attribute_value_id']);
        
        return $stmt->execute();
    }

    public function removeAttribute($id) {
        $sql = "DELETE FROM product_attributes WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function removeProductAttributes($product_id) {
        $sql = "DELETE FROM product_attributes WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':product_id', $product_id);
        
        return $stmt->execute();
    }

    // Método para buscar variantes por atributos específicos
    public function findVariantsByAttributes($parent_id, $attributes) {
        $placeholders = [];
        $values = [];
        
        foreach ($attributes as $attr_id => $value_id) {
            $placeholders[] = "(:attr{$attr_id}, :val{$attr_id})";
            $values["attr{$attr_id}"] = $attr_id;
            $values["val{$attr_id}"] = $value_id;
        }
        
        $placeholdersStr = implode(',', $placeholders);
        
        $sql = "SELECT p.* FROM products p
                WHERE p.parent_id = :parent_id
                AND p.id IN (
                    SELECT pa.product_id 
                    FROM product_attributes pa
                    WHERE (pa.attribute_id, pa.attribute_value_id) IN ({$placeholdersStr})
                    GROUP BY pa.product_id
                    HAVING COUNT(*) = :count
                )";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->bindParam(':count', count($attributes));
        
        foreach ($values as $key => $value) {
            $stmt->bindParam(":{$key}", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}