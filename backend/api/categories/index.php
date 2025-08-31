<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../utils/Validator.php';

$method = $_SERVER['REQUEST_METHOD'];
$category = new Category();

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener categoría específica
                $result = $category->getById($_GET['id']);
                if ($result) {
                    Response::success($result);
                } else {
                    Response::error('Categoría no encontrada', 404);
                }
            } elseif (isset($_GET['parent_id'])) {
                // Obtener hijos de una categoría
                $result = $category->getChildren($_GET['parent_id']);
                Response::success($result);
            } else {
                // Obtener todas las categorías
                $active_only = !isset($_GET['include_inactive']);
                $result = $category->getAll($active_only);
                Response::success($result);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            Validator::required($input['name'], 'name');
            Validator::maxLength($input['name'], 100, 'name');
            
            // Generar slug si no se proporciona
            if (empty($input['slug'])) {
                $input['slug'] = strtolower(str_replace(' ', '-', $input['name']));
            }
            
            // Valores por defecto
            $data = [
                'name' => $input['name'],
                'slug' => $input['slug'],
                'description' => $input['description'] ?? null,
                'image_url' => $input['image_url'] ?? null,
                'parent_id' => $input['parent_id'] ?? null,
                'level' => $input['level'] ?? 0,
                'is_active' => $input['is_active'] ?? true
            ];
            
            $result = $category->create($data);
            if ($result) {
                Response::success($result, 'Categoría creada exitosamente');
            } else {
                Response::error('Error al crear la categoría');
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                Response::error('ID de categoría requerido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            Validator::required($input['name'], 'name');
            Validator::maxLength($input['name'], 100, 'name');
            
            $data = [
                'name' => $input['name'],
                'slug' => $input['slug'],
                'description' => $input['description'] ?? null,
                'image_url' => $input['image_url'] ?? null,
                'parent_id' => $input['parent_id'] ?? null,
                'level' => $input['level'] ?? 0,
                'is_active' => $input['is_active'] ?? true
            ];
            
            $result = $category->update($_GET['id'], $data);
            if ($result) {
                Response::success(null, 'Categoría actualizada exitosamente');
            } else {
                Response::error('Error al actualizar la categoría');
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                Response::error('ID de categoría requerido');
            }
            
            $result = $category->delete($_GET['id']);
            if ($result) {
                Response::success(null, 'Categoría eliminada exitosamente');
            } else {
                Response::error('Error al eliminar la categoría');
            }
            break;

        default:
            Response::error('Método no permitido', 405);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}