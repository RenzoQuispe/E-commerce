<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../models/Product.php'; // require_once '../../models/Product.php'
require_once __DIR__ . '/../../utils/Validator.php';

$method = $_SERVER['REQUEST_METHOD'];
$product = new Product();

try {
    switch($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $product->getById($_GET['id']);
                if ($result) {
                    Response::success($result);
                } else {
                    Response::error('Producto no encontrado', 404);
                }
            } else {
                // Aplicar filtros
                $filters = [];
                if (isset($_GET['active_only'])) $filters['active_only'] = true;
                if (isset($_GET['product_type'])) $filters['product_type'] = $_GET['product_type'];
                if (isset($_GET['category_id'])) $filters['category_id'] = $_GET['category_id'];
                if (isset($_GET['parent_id'])) $filters['parent_id'] = $_GET['parent_id'];
                if (isset($_GET['featured'])) $filters['featured'] = $_GET['featured'] == '1';
                if (isset($_GET['limit'])) $filters['limit'] = intval($_GET['limit']);
                if (isset($_GET['offset'])) $filters['offset'] = intval($_GET['offset']);
                
                $result = $product->getAll($filters);
                Response::success($result);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones básicas
            Validator::required($input['name'], 'name');
            Validator::required($input['category_id'], 'category_id');
            Validator::required($input['sku'], 'sku');
            Validator::maxLength($input['name'], 200, 'name');
            
            // Validar tipos de producto
            $valid_types = ['simple', 'parent', 'variant'];
            Validator::inArray($input['product_type'] ?? 'simple', $valid_types, 'product_type');
            
            // Generar slug si no se proporciona
            if (empty($input['slug'])) {
                $input['slug'] = strtolower(str_replace(' ', '-', $input['name']));
            }
            
            $data = [
                'parent_id' => $input['parent_id'] ?? null,
                'product_type' => $input['product_type'] ?? 'simple',
                'name' => $input['name'],
                'slug' => $input['slug'],
                'description' => $input['description'] ?? null,
                'short_description' => $input['short_description'] ?? null,
                'category_id' => $input['category_id'],
                'brand' => $input['brand'] ?? null,
                'model' => $input['model'] ?? null,
                'sku' => $input['sku'],
                'barcode' => $input['barcode'] ?? null,
                'price' => $input['price'] ?? null,
                'compare_price' => $input['compare_price'] ?? null,
                'cost_price' => $input['cost_price'] ?? null,
                'stock_quantity' => $input['stock_quantity'] ?? 0,
                'manage_stock' => $input['manage_stock'] ?? true,
                'stock_status' => $input['stock_status'] ?? 'instock',
                'is_active' => $input['is_active'] ?? true,
                'is_featured' => $input['is_featured'] ?? false,
                'featured_image' => $input['featured_image'] ?? null,
                'image_gallery' => isset($input['image_gallery']) ? json_encode($input['image_gallery']) : null
            ];
            
            $result = $product->create($data);
            if ($result) {
                Response::success($result, 'Producto creado exitosamente');
            } else {
                Response::error('Error al crear el producto');
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                Response::error('ID de producto requerido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validaciones
            Validator::required($input['name'], 'name');
            Validator::required($input['category_id'], 'category_id');
            Validator::maxLength($input['name'], 200, 'name');
            
            $data = [
                'name' => $input['name'],
                'slug' => $input['slug'],
                'description' => $input['description'] ?? null,
                'short_description' => $input['short_description'] ?? null,
                'category_id' => $input['category_id'],
                'brand' => $input['brand'] ?? null,
                'model' => $input['model'] ?? null,
                'sku' => $input['sku'],
                'barcode' => $input['barcode'] ?? null,
                'price' => $input['price'] ?? null,
                'compare_price' => $input['compare_price'] ?? null,
                'cost_price' => $input['cost_price'] ?? null,
                'stock_quantity' => $input['stock_quantity'] ?? 0,
                'manage_stock' => $input['manage_stock'] ?? true,
                'stock_status' => $input['stock_status'] ?? 'instock',
                'is_active' => $input['is_active'] ?? true,
                'is_featured' => $input['is_featured'] ?? false,
                'featured_image' => $input['featured_image'] ?? null,
                'image_gallery' => isset($input['image_gallery']) ? json_encode($input['image_gallery']) : null
            ];
            
            $result = $product->update($_GET['id'], $data);
            if ($result) {
                Response::success(null, 'Producto actualizado exitosamente');
            } else {
                Response::error('Error al actualizar el producto');
            }
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                Response::error('ID de producto requerido');
            }
            
            $result = $product->delete($_GET['id']);
            if ($result) {
                Response::success(null, 'Producto eliminado exitosamente');
            } else {
                Response::error('Error al eliminar el producto');
            }
            break;

        default:
            Response::error('Método no permitido', 405);
    }
} catch (Exception $e) {
    Response::error($e->getMessage());
}