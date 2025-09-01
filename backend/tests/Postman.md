# Test de endpoints

## Pasos previos:

1. Crear base de datos y configurar archivo config/Database.php.

    Ejemplo:

    ```php
    private $host = 'localhost';
    private $db_name = 'okea';
    private $username = 'root';
    private $password = 'contraseña123';
    ```

2. Preparar servidor:

    ```
    cd backend
    php -S localhost:8000
    ```

## Probar gestión de categorías

### Obtener todas las categorías: 

GET - `http://localhost:8000/api/categories`

### Obtener categoría por ID: 

GET - `http://localhost:8000/api/categories?id=2`

### Obtener categorías hijo: 

GET - `http://localhost:8000/api/categories?parent_id=2`

### Crear Categoría padre (level 0): 

POST - `http://localhost:8000/api/categories`

```
{
    "name": "Juguetes",
    "slug": "juguetes"
}
```
### Crear Subcategoría (level >= 1): Crear subcategoría Laptop de Electrónicos

POST - `http://localhost:8000/api/categories`

```
{
    "name": "Laptops",
    "slug": "laptops",
    "level": 1,
    "parent_id": 2
}
```
### Actualizar categorías:

PUT - `http://localhost:8000/api/categories?id=8`

```
{
    "name": "Calzado",
    "slug": "calzado",
    "description": "Zapatos y zapatillas",
    "level": 1,
    "parent_id": 1
}
```
### Eliminar categoría: 

DELETE - `http://localhost:8000/api/categories?id=20`

## Probar gestión de productos

### Obtener todos las productos: 

GET - `http://localhost:8000/api/products`

### Obtener producto por ID: 

GET - `http://localhost:8000/api/products?id=16`

### Obtener variantes de un producto padre: 

GET - `http://localhost:8000/api/products?parent_id=5`

### Obtener productos con filtros extra: 

GET - `http://localhost:8000/api/products?active_only=1&product_type=parent`

### Crear producto simple: 

POST - `http://localhost:8000/api/products`

```
{
    "product_type": "simple",
    "name": "iPhone 14 Pro",
    "slug": "iphone-14-pro",
    "sku": "IPH14PRO128",
    "category_id": 1,
    "brand": "Apple",
    "model": "A2890",
    "price": 999.99,
    "stock_quantity": 10,
    "is_featured": true
}
```

### Crear producto padre: 

POST - `http://localhost:8000/api/products`

```
{
    "product_type": "parent",
    "name": "Balon de Futbol",
    "slug": "balon-de-futbol",
    "sku": "BAL-FUT-AD",
    "category_id": 18,
    "brand": "Adidas",
    "price": 59.99,
    "stock_quantity": 0,
    "is_featured": true
}
```

### Crear producto variante: 

POST - `http://localhost:8000/api/products`

```
{
    "product_type": "variant",
    "name": "Balon de Futbol Azul",
    "slug": "balon-de-futbol-azul",
    "sku": "BAL-FUT-AD-AZU",
    "category_id": 18,
    "parent_id": 27,
    "brand": "Adidas",
    "price": 59.99,
    "stock_quantity": 10,
    "is_active": true,
    "is_featured": 0
}
```

### Actualizar producto:

PUT - `http://localhost:8000/api/products?id=28`

```
{
    "name": "Balon de Futbol Adidas Azul",
    "slug": "balon-de-futbol-adidas-azul",
    "sku": "BAL-FUT-AD-AZU",
    "category_id": 18,
    "price": 69.99,
    "stock_quantity": 35,
    "is_featured": 0
}
```
### Eliminar producto: 

DELETE - `http://localhost:8000/api/products?id=4`

## Probar gestión de atributos

### Obtener todos las atributos: 

GET - `http://localhost:8000/api/attributes`

### Obtener atributo por ID: 

GET - `http://localhost:8000/api/attributes?id=1`

### Obtener atributo con sus valores: 

GET - `http://localhost:8000/api/attributes?id=1&with_values=1`

### Crear atributo: 

POST - `http://localhost:8000/api/attributes`

```
{
    "name": "Tamaño",
    "slug": "tamaño-juguete",
    "is_variation": 1
}
```

### Actualizar atributo:

PUT - `http://localhost:8000/api/attributes?id=5`

```
{
    "name": "Tamaño-juguete",
    "slug": "tamaño-juguete"
}
```
### Eliminar atributo: 

DELETE - `http://localhost:8000/api/attributes?id=5`

## Probar gestión de valores de atributos

### Obtener valores de un atributo

GET - `http://localhost:8000/api/attribute-values?attribute_id=3`

### Obtener atributo por ID

GET - `http://localhost:8000/api/attribute-values?id=5`

### Crear un valor de atributo (Color Amarillo)

POST - `http://localhost:8000/api/attribute-values`

```
{
    "attribute_id": 1,
    "value": "Amarillo",
    "slug": "amarillo"
}
```

### Actualizar valor de un atributo(Rojo -> Rojo Intenso)

PUT - `http://localhost:8000/api/attribute-values?id=4`

```
{
    "value": "Rojo Intenso",
    "slug": "rojo-intenso"
}
```

### Eliminar un valor de atributo

DELETE - `http://localhost:8000/api/attribute-values?id=6`

## Probar gestión de relación de Producto-Atributos

### Obtener todos los atributos de un producto (CAM-BASIC-NEG-L  ->  Color Negro y Talla L)

GET - `http://localhost:8000/api/product-attributes?product_id=8`

### Encontrar variantes por atributo

Corregir Error: `http://localhost:8000/api/product-attributes?parent_id=2&attributes={\"1\":\"1\",\"2\":\"1\"}`

### Asignar un solo atributo a un producto especifico

POST - `http://localhost:8000/api/product-attributes`

Producto con id=16, ahora tambien tiene el atributo de color=blanco

```
{
    "product_id": 16,
    "attribute_id": 1,
    "attribute_value_id": 2
}
```

Podemos revisar los atributos de un producto con : `http://localhost:8000/api/product-attributes?product_id=16`

### Asignar multiples atributos

PROCESO DE CREAR PRODUCTO CON SUS ATRIBUTOS:

- Creacion de producto: POST - `http://localhost:8000/api/products`

```
{
    "product_type": "variant",
    "name": "Zapatillas Running Elite Verde 42",
    "slug": "zapatillas-running-elite-verde-42",
    "sku": "ZAP-RUN-VER-42",
    "category_id": 18,
    "parent_id": 19,
    "price": 129.99,
    "stock_quantity": 10,
    "is_active": true,
    "is_featured": 0
}
```
    Se creo un producto variante con id=30

- Configurar sus atributos: POST - `http://localhost:8000/api/product-attributes`

```
{ 
    "multiple": true,  
    "product_id": 30,
    "attributes":   [    {      "attribute_id": 1,      "attribute_value_id": 5    },    
                         {      "attribute_id": 4,      "attribute_value_id": 19    }  
                    ]
}

```

- Revisamos con: GET - `http://localhost:8000/api/product-attributes?product_id=30`

### Actualizar relación Producto-Atributo

Actualizar color de producto Camiseta Básica Unisex - Negro S (ID=6) : Color Negro -> Color Verde(ID=5). La relación Producto-Atributo esta en la tabla product_attributes ID=1 (product_id=6, attribute_id=1, attribute_value_id=1)

PUT - `http://localhost:8000/api/product-attributes?id=1`

```
{ 
    "attribute_value_id": 5
}
```
Ahora tenemos en la tabla product_attributes ID=1 (product_id=6, attribute_id=1, attribute_value_id=5) y si revisamos con GET-`http://localhost:8000/api/product-attributes?product_id=6`  tenemos los valores actualizados, pero no estaria actualizado el name, slug, sku, description (valores que describen tambien el valor anterior del atributo modificado). En principio este endpoint no sera util, solo sera de utilidad el de crear y eliminar.

### Eliminar un solo atributo de producto

DELETE - `http://localhost:8000/api/product-attributes?id=37`

La relación Producto-Atributo se elimina pero al igual que el caso anterior, no estaria actualizado el name, slug, sku, description, etc. La idea es que al eliminar un atributo, tambien eliminaremos el producto variante correspondiente a dicho valor del atributo eliminado.

### Eliminar todos los atributos del producto

DELETE - `http://localhost:8000/api/product-attributes?product_id=9`

Elimina todos las relaciones Producto-Atributo correspondientes al producto con ID=9 en la tabla product_attributes, pero en tabla products aun existe el producto con ID=9. La idea seria tras eliminar el ese valor de atributo(Correspondiente a la variante de producto) tambien se eliminaria el producto correspondiente. El proceso a la inversa si es completo, si eliminamos un producto, todos las relaciones correspondientes a dicho producto en la tabla product_attributes se eliminan automaticamente.

