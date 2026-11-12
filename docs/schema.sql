-- MANXMARKET Database Schema (MySQL 8.0 / InnoDB)
-- Host: 127.0.0.1:3306, Database: db_manxmarket

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS receivable_payments;
DROP TABLE IF EXISTS receivables;
DROP TABLE IF EXISTS stock_movements;
DROP TABLE IF EXISTS transaction_items;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS wholesale_rules;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS store_settings;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('owner', 'kasir', 'gudang', 'pembeli') NOT NULL DEFAULT 'kasir',
    credit_limit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT NULL,
    sku VARCHAR(60) NOT NULL UNIQUE,
    barcode VARCHAR(60) NULL,
    name VARCHAR(200) NOT NULL,
    unit VARCHAR(30) NOT NULL DEFAULT 'pcs',
    cost_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    retail_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 5,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_products_barcode (barcode),
    INDEX idx_products_sku (sku),
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE wholesale_rules (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NULL,
    rule_type ENUM('per_item_qty', 'total_purchase_amount', 'total_purchase_qty') NOT NULL,
    min_qty INT NOT NULL DEFAULT 0,
    min_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount_type ENUM('fixed_amount', 'percentage', 'special_price') NOT NULL,
    discount_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wholesale_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transactions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(60) NOT NULL UNIQUE,
    cashier_id BIGINT NOT NULL,
    customer_id BIGINT NULL,
    customer_name_manual VARCHAR(100) NULL,
    transaction_date DATETIME NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_discount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    change_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('tunai', 'qris', 'hutang') NOT NULL,
    payment_status ENUM('lunas', 'belum_lunas', 'void') NOT NULL,
    void_by BIGINT NULL,
    void_reason TEXT NULL,
    void_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trans_invoice (invoice_number),
    INDEX idx_trans_date (transaction_date),
    CONSTRAINT fk_trans_cashier FOREIGN KEY (cashier_id) REFERENCES users (id),
    CONSTRAINT fk_trans_customer FOREIGN KEY (customer_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_trans_void_by FOREIGN KEY (void_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transaction_items (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    product_name_snapshot VARCHAR(200) NOT NULL,
    sku_snapshot VARCHAR(60) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    cost_price_snapshot DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    unit_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    discount_per_item DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    subtotal_item DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_item_transaction FOREIGN KEY (transaction_id) REFERENCES transactions (id) ON DELETE CASCADE,
    CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stock_movements (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    reference_id BIGINT NULL,
    movement_type ENUM('in', 'sale', 'void_return', 'adjustment') NOT NULL,
    qty_change INT NOT NULL,
    stock_before INT NOT NULL,
    stock_after INT NOT NULL,
    notes VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_movement_product FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT fk_movement_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE receivables (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT NOT NULL UNIQUE,
    customer_id BIGINT NOT NULL,
    total_debt DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    total_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    remaining_debt DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    due_date DATE NOT NULL,
    status ENUM('belum_lunas', 'lunas', 'void') NOT NULL DEFAULT 'belum_lunas',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_receivable_trans FOREIGN KEY (transaction_id) REFERENCES transactions (id) ON DELETE CASCADE,
    CONSTRAINT fk_receivable_customer FOREIGN KEY (customer_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE receivable_payments (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    receivable_id BIGINT NOT NULL,
    payment_number VARCHAR(60) NOT NULL UNIQUE,
    received_by BIGINT NOT NULL,
    payment_date DATETIME NOT NULL,
    amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('tunai', 'qris') NOT NULL,
    reference_note VARCHAR(100) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_receivable FOREIGN KEY (receivable_id) REFERENCES receivables (id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_receiver FOREIGN KEY (received_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE store_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
