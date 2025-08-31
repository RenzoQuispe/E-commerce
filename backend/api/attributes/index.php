<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../models/Attributes.php';
require_once __DIR__ . '/../../utils/Validator.php';

$method = $_SERVER['REQUEST_METHOD'];
$attribute = new Attributes();

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                if (isset($_GET['with_values'])) {
                    $result = $attribute->getWithValues($_GET['id']);
                } else {
                    $result = $attribute->getById($_GET['id']);
                }
                
                if ($result) {
                    Response::success($result);
                } else {
                    Response::error('Atributo no encontrado', 404);
                }
            } else {
                $variation_only = isset($_GET['variation_only']);
                $result = $attribute->getAll($variation_only);
                Response::success($result);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            Validator::required($input['name'], 'name');
            Validator::maxLength($input['name'], 100, 'name');
            
            if (empty($input['slug'])) {
                $input['slug'] = strtolower(str_replace(' ', '-', $input['name']));
            }
            
            $data = [
                'name' => $input['name'],
                'slug' => $input['slug'],
                'is_variation' => $input['is_variation'] ?? true
            ];
            
            $result = $attribute->create($data);
            if ($result) {
                Response::success($result, 'Atributo creado exitosamente');
            } else {
                Response::error('Error al crear el atributo');
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                Response::error('ID de atributo requerido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            Validator::required($input['name'], 'name');
            Validator::maxLength($input['name'], 100, 'name');
            
            $data = [
                'name' => $input['name'],
                'slug' => $input['slug'],
                'is_variation' => $input['is_variation'] ?? true
            ];
            
            $result = $attribute->update($_GET['id'], $data);
            if ($result) {
                Response::success(null, 'Atributo actualizado exitosamente');
            } else {
                Response::error('Error al actualizar el atributo');
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                Response::error('ID de atributo requerido');
            }
            
            $result = $attribute->delete($_GET['id']);
            if ($result) {
                Response::success(null, 'Atributo eliminado exitosamente');
            } else {
                Response::error('Error al eliminar el atributo');
            }
            break;

        default:
            Response::error('Método no permitido', 405);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}