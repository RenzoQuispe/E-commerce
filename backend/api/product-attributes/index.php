<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../models/ProductAttribute.php';
require_once __DIR__ . '/../../utils/Validator.php';

$method = $_SERVER['REQUEST_METHOD'];
$productAttribute = new ProductAttribute();

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener un atributo específico de producto
                $result = $productAttribute->getById($_GET['id']);
                if ($result) {
                    Response::success($result);
                } else {
                    Response::error('Atributo de producto no encontrado', 404);
                }
            } elseif (isset($_GET['product_id'])) {
                // Obtener todos los atributos de un producto
                $result = $productAttribute->getByProduct($_GET['product_id']);
                Response::success($result);
            } elseif (isset($_GET['parent_id']) && isset($_GET['attributes'])) {
                // Buscar variantes por atributos específicos
                $attributes = json_decode($_GET['attributes'], true);
                $result = $productAttribute->findVariantsByAttributes($_GET['parent_id'], $attributes);
                Response::success($result);
            } else {
                Response::error('product_id requerido');
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['multiple']) && $input['multiple']) {
                // Asignar múltiples atributos a un producto
                Validator::required($input['product_id'], 'product_id');
                Validator::required($input['attributes'], 'attributes');
                
                if (!is_array($input['attributes']) || empty($input['attributes'])) {
                    Response::error('attributes debe ser un array no vacío');
                }
                
                // Validar cada atributo
                foreach ($input['attributes'] as $attr) {
                    Validator::required($attr['attribute_id'], 'attribute_id');
                    Validator::required($attr['attribute_value_id'], 'attribute_value_id');
                }
                
                $result = $productAttribute->assignMultipleAttributes(
                    $input['product_id'], 
                    $input['attributes']
                );
                
                if ($result) {
                    Response::success($result, 'Atributos asignados exitosamente');
                } else {
                    Response::error('Error al asignar los atributos');
                }
            } else {
                // Asignar un solo atributo
                Validator::required($input['product_id'], 'product_id');
                Validator::required($input['attribute_id'], 'attribute_id');
                Validator::required($input['attribute_value_id'], 'attribute_value_id');
                
                $data = [
                    'product_id' => $input['product_id'],
                    'attribute_id' => $input['attribute_id'],
                    'attribute_value_id' => $input['attribute_value_id']
                ];
                
                $result = $productAttribute->assignAttribute($data);
                if ($result) {
                    Response::success($result, 'Atributo asignado exitosamente');
                } else {
                    Response::error('Error al asignar el atributo');
                }
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                Response::error('ID de atributo de producto requerido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            Validator::required($input['attribute_value_id'], 'attribute_value_id');
            
            $data = [
                'attribute_value_id' => $input['attribute_value_id']
            ];
            
            $result = $productAttribute->updateAttribute($_GET['id'], $data);
            if ($result) {
                Response::success(null, 'Atributo actualizado exitosamente');
            } else {
                Response::error('Error al actualizar el atributo');
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                // Eliminar un atributo específico
                $result = $productAttribute->removeAttribute($_GET['id']);
                if ($result) {
                    Response::success(null, 'Atributo eliminado exitosamente');
                } else {
                    Response::error('Error al eliminar el atributo');
                }
            } elseif (isset($_GET['product_id'])) {
                // Eliminar todos los atributos de un producto
                $result = $productAttribute->removeProductAttributes($_GET['product_id']);
                if ($result) {
                    Response::success(null, 'Todos los atributos del producto eliminados exitosamente');
                } else {
                    Response::error('Error al eliminar los atributos del producto');
                }
            } else {
                Response::error('ID o product_id requerido');
            }
            break;

        default:
            Response::error('Método no permitido', 405);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}