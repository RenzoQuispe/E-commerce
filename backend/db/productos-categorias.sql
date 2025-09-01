-- TABLA DE CATEGORÍAS
CREATE TABLE categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    parent_id BIGINT UNSIGNED NULL,
    level TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    UNIQUE KEY uk_categories_slug (slug),
    INDEX idx_categories_parent_active (parent_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA PRINCIPAL: PRODUCTOS HÍBRIDOS
-- Un registro puede ser producto simple o producto padre o variante
CREATE TABLE products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    
    -- ARQUITECTURA HÍBRIDA CLAVE
    parent_id BIGINT UNSIGNED NULL, -- NULL = producto simple o padre, NOT NULL = es variante
    product_type ENUM('simple', 'parent', 'variant') NOT NULL DEFAULT 'simple',
    
    -- Información básica (aplica a todos los tipos)
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    description TEXT,
    
    -- Relación con categoría
    category_id BIGINT UNSIGNED NOT NULL,
    
    -- Info comercial
    brand VARCHAR(100),
    model VARCHAR(100),
    
    -- IDENTIFICACIÓN UNIFICADA
    sku VARCHAR(100) NOT NULL, -- Único para simples y variantes, prefijo para padres
    barcode VARCHAR(50),
    
    -- PRECIOS (aplica a simples y variantes)
    price DECIMAL(10,2) UNSIGNED,
    compare_price DECIMAL(10,2) UNSIGNED,
    cost_price DECIMAL(10,2) UNSIGNED,
    
    -- INVENTARIO UNIFICADO (solo para simples y variantes)
    stock_quantity INT DEFAULT 0,
    manage_stock BOOLEAN DEFAULT TRUE,
    stock_status ENUM('instock', 'outofstock', 'backorder') DEFAULT 'instock',
    
    -- Configuración de producto
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    
    -- Imágenes unificadas
    featured_image VARCHAR(255),
    image_gallery JSON, -- ["url1.jpg", "url2.jpg", "url3.jpg"]
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    
    -- Clave foránea auto-referencial para variantes
    FOREIGN KEY (parent_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    
    -- RESTRICCIONES DE INTEGRIDAD ARQUITECTÓNICA
    UNIQUE KEY uk_products_slug (slug),
    UNIQUE KEY uk_products_sku (sku),
    
    -- ÍNDICES OPTIMIZADOS PARA QUERIES HÍBRIDAS
    INDEX idx_products_type_active (product_type, is_active),
    INDEX idx_products_parent_active (parent_id, is_active), -- Para variantes
    INDEX idx_products_category_type (category_id, product_type),
    INDEX idx_products_stock_simple (product_type, stock_quantity), -- Solo simples y variantes
    INDEX idx_products_featured (is_featured, is_active),
    
    -- VALIDACIONES ARQUITECTÓNICAS
    CONSTRAINT chk_parent_logic CHECK (
        (product_type = 'simple' AND parent_id IS NULL) OR
        (product_type = 'parent' AND parent_id IS NULL) OR  
        (product_type = 'variant' AND parent_id IS NOT NULL)
    ),
    CONSTRAINT chk_stock_logic CHECK (
        (product_type = 'parent' AND stock_quantity = 0) OR
        (product_type IN ('simple', 'variant'))
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE ATRIBUTOS
CREATE TABLE attributes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL, -- Color, Talla, Material
    slug VARCHAR(100) NOT NULL, -- color, talla, material
    is_variation BOOLEAN DEFAULT TRUE, -- Se usa para crear variantes
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_attributes_slug (slug),
    INDEX idx_attributes_variation (is_variation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE TÉRMINOS/VALORES
CREATE TABLE attribute_values (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    attribute_id BIGINT UNSIGNED NOT NULL,
    value VARCHAR(100) NOT NULL, -- Rojo, M, Algodón
    slug VARCHAR(100) NOT NULL, -- rojo, m, algodon
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE,
    UNIQUE KEY uk_terms_attribute_slug (attribute_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA DE RELACIÓN PRODUCTO-ATRIBUTOS
-- Solo para productos tipo 'variant' y opcionalmente 'simple'
CREATE TABLE product_attributes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    attribute_id BIGINT UNSIGNED NOT NULL,
    attribute_value_id BIGINT UNSIGNED NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES attributes(id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_value_id) REFERENCES attribute_values(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_product_attribute (product_id, attribute_id),
    INDEX idx_product_attributes_product (product_id),
    INDEX idx_product_attributes_term (attribute_value_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
