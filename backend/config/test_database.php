<?php
require_once __DIR__ . "/Database.php";

$db = new Database();

$conn = $db->getConnection(); // tener driver PDO MySQL

if ($conn) {
    echo "Conexión exitosa a la base de datos.<br>";
    
    // Ejecutar una consulta simple para verificar la conexión
    $sql = "SELECT NOW() AS fecha_actual";
    
    $stmt = $conn->query($sql);
    $fila = $stmt->fetch();
    echo "La fecha en MySQL es: " . $fila['fecha_actual'];
} else {
    echo "No se pudo conectar.\n";
}
