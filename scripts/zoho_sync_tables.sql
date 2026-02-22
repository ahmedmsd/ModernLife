-- ============================================================
-- Zoho CRM Sync Tables - Modern Life
-- ============================================================


-- ============================================================
-- 1. إضافة أعمدة Zoho للعملاء (clients)
-- ============================================================

ALTER TABLE `clients`
    ADD COLUMN IF NOT EXISTS `zoho_account_id` VARCHAR(100) NULL AFTER `client_id`,
    ADD COLUMN IF NOT EXISTS `zoho_contact_id` VARCHAR(100) NULL AFTER `zoho_account_id`;

ALTER IGNORE TABLE `clients`
    ADD UNIQUE KEY `clients_zoho_account_id_unique` (`zoho_account_id`),
    ADD UNIQUE KEY `clients_zoho_contact_id_unique` (`zoho_contact_id`);


-- ============================================================
-- 2. جدول التسعيرات (quotations)
-- ============================================================

CREATE TABLE IF NOT EXISTS `quotations` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `zoho_quote_id` VARCHAR(100)    NOT NULL,
    `subject`       VARCHAR(500)    NULL,
    `zoho_module`   VARCHAR(100)    NULL,
    `contract_type` VARCHAR(100)    NULL,
    `quote_number`  VARCHAR(100)    NULL,
    `quote_stage`   VARCHAR(100)    NULL,
    `valid_till`    DATE            NULL,
    `total_amount`  DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `sub_total`     DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `tax`           DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `adjustment`    DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `discount`      DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `client_id`     INT             NULL,
    `raw_data`      LONGTEXT        NULL,
    `created_at`    TIMESTAMP       NULL,
    `updated_at`    TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `quotations_zoho_quote_id_unique` (`zoho_quote_id`),
    INDEX `quotations_zoho_module_index` (`zoho_module`),
    INDEX `quotations_contract_type_index` (`contract_type`),
    INDEX `quotations_client_id_index` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. جدول بنود التسعيرات (quotation_items)
-- ============================================================

CREATE TABLE IF NOT EXISTS `quotation_items` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quotation_id`      BIGINT UNSIGNED NOT NULL,
    `zoho_line_item_id` VARCHAR(100)    NULL,
    `product_id`        VARCHAR(100)    NULL,
    `product_name`      VARCHAR(500)    NULL,
    `description`       TEXT            NULL,
    `quantity`          DECIMAL(15, 2)  NOT NULL DEFAULT 1,
    `list_price`        DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `unit_price`        DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `discount`          DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `tax`               DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `total`             DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `quotation_items_quotation_id_index` (`quotation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. جدول أوامر البيع / المشاريع (sales_orders)
-- ============================================================

CREATE TABLE IF NOT EXISTS `sales_orders` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `zoho_so_id`   VARCHAR(100)    NOT NULL,
    `subject`      VARCHAR(500)    NULL,
    `so_number`    VARCHAR(100)    NULL,
    `status`       VARCHAR(100)    NULL,
    `zoho_module`  VARCHAR(100)    NULL,
    `total_amount` DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `sub_total`    DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `tax`          DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `adjustment`   DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `discount`     DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `client_id`    INT             NULL,
    `raw_data`     LONGTEXT        NULL,
    `created_at`   TIMESTAMP       NULL,
    `updated_at`   TIMESTAMP       NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `sales_orders_zoho_so_id_unique` (`zoho_so_id`),
    INDEX `sales_orders_zoho_module_index` (`zoho_module`),
    INDEX `sales_orders_client_id_index` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. جدول بنود أوامر البيع (sales_order_items)
-- ============================================================

CREATE TABLE IF NOT EXISTS `sales_order_items` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sales_order_id`    BIGINT UNSIGNED NOT NULL,
    `zoho_line_item_id` VARCHAR(100)    NULL,
    `product_id`        VARCHAR(100)    NULL,
    `product_name`      VARCHAR(500)    NULL,
    `description`       TEXT            NULL,
    `quantity`          DECIMAL(15, 2)  NOT NULL DEFAULT 1,
    `list_price`        DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `unit_price`        DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `discount`          DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `tax`               DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    `total`             DECIMAL(15, 2)  NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX `sales_order_items_sales_order_id_index` (`sales_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ✅ تحقق
SHOW TABLES LIKE 'quotation%';
SHOW TABLES LIKE 'sales_order%';

