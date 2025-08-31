<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../models/Attributes.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../models/AttributeValue.php';

$method = $_SERVER['REQUEST_METHOD'];
$attributeValue = new AttributeValue();

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $attributeValue->getById($_GET['id']);
                if ($result) {
                    Response::success($result);
                } else {
                    Response::error('Valor de atributo no encontrado', 404);
                }
            } elseif (isset($_GET['attribute_id'])) {
                $result = $attributeValue->getByAttribute($_GET['attribute_id']);
                Response::success($result);
            } else {
                Response::error('ID de atributo requerido');
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            Validator::required($input['attribute_id'], 'attribute_id');
            Validator::required($input['value'], 'value');
            Validator::maxLength($input['value'], 100, 'value');
            
            if (empty($input['slug'])) {
                $input['slug'] = strtolower(str_replace(' ', '-', $input['value']));
            }
            
            $data = [
                'attribute_id' => $input['attribute_id'],
                'value' => $input['value'],
                'slug' => $input['slug']
            ];
            
            $result = $attributeValue->create($data);
            if ($result) {
                Response::success($result, 'Valor de atributo creado exitosamente');
            } else {
                Response::error('Error al crear el valor de atributo');
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                Response::error('ID de valor de atributo requerido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            Validator::required($input['value'], 'value');
            Validator::maxLength($input['value'], 100, 'value');
            
            $data = [
                'value' => $input['value'],
                'slug' => $input['slug']
            ];
            
            $result = $attributeValue->update($_GET['id'], $data);
            if ($result) {
                Response::success(null, 'Valor de atributo actualizado exitosamente');
            } else {
                Response::error('Error al actualizar el valor de atributo');
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                Response::error('ID de valor de atributo requerido');
            }
            
            $result = $attributeValue->delete($_GET['id']);
            if ($result) {
                Response::success(null, 'Valor de atributo eliminado exitosamente');
            } else {
                Response::error('Error al eliminar el valor de atributo');
            }
            break;

        default:
            Response::error('Método no permitido', 405);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}