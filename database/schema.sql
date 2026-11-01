SET foreign_key_checks = 0;

DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS purchases;
DROP TABLE IF EXISTS productions;
DROP TABLE IF EXISTS side_dishes;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(50) NOT NULL,
    encrypted_password VARCHAR(255) NOT NULL,
    avatar_name VARCHAR(65),
    role ENUM('admin', 'attendant') NOT NULL DEFAULT 'attendant',
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    UNIQUE KEY uq_products_name (name)
) ENGINE=InnoDB;

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    UNIQUE KEY uq_customers_phone (phone)
) ENGINE=InnoDB;

CREATE TABLE side_dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    UNIQUE KEY uq_side_dishes_name (name)
) ENGINE=InnoDB;

CREATE TABLE productions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    production_date DATE NOT NULL,
    quantity INT NOT NULL,
    price_per_kg DECIMAL(10, 2) NOT NULL,
    UNIQUE KEY uq_productions_product_id_production_date (product_id, production_date),
    CONSTRAINT fk_productions_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    purchase_date DATE NOT NULL,
    quantity INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    price_per_kg DECIMAL(10, 2),
    CONSTRAINT fk_purchases_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    status ENUM('ordered', 'picked_up', 'cancelled') NOT NULL DEFAULT 'ordered',
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_orders_customer_id
        FOREIGN KEY (customer_id) REFERENCES customers (id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    CONSTRAINT fk_order_items_order_id
        FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    payment_method ENUM('cash', 'pix', 'card') NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_sales_order_id
        FOREIGN KEY (order_id) REFERENCES orders (id)
) ENGINE=InnoDB;

CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    weight DECIMAL(10, 3) NOT NULL,
    CONSTRAINT fk_sale_items_sale_id
        FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE CASCADE,
    CONSTRAINT fk_sale_items_product_id
        FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

SET foreign_key_checks = 1;
