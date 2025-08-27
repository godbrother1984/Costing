-- =======================================
-- FG PRICING SYSTEM - DATABASE STRUCTURE
-- Phase 2: Complete Database Setup
-- =======================================

-- Drop existing database if exists and create new one
DROP DATABASE IF EXISTS fg_pricing;
CREATE DATABASE fg_pricing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fg_pricing;

-- =======================================
-- 1. USERS & AUTHENTICATION TABLES
-- =======================================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'sales', 'viewer') DEFAULT 'sales',
    department VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =======================================
-- 2. MASTER DATA TABLES
-- =======================================

-- Customers Table (Cached from D365)
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_group VARCHAR(50),
    contact_person VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    credit_limit DECIMAL(15,2) DEFAULT 0,
    payment_terms VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    synced_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_customer_id (customer_id),
    INDEX idx_customer_group (customer_group),
    INDEX idx_active (is_active)
);

-- Products Table (Cached from D365)
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id VARCHAR(50) UNIQUE NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_group_id VARCHAR(50),
    unit_id VARCHAR(10),
    product_type ENUM('FG', 'RM', 'SFG') DEFAULT 'FG',
    standard_cost DECIMAL(12,4) DEFAULT 0,
    sales_price DECIMAL(12,4) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT,
    specifications JSON,
    synced_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_item_id (item_id),
    INDEX idx_item_group (item_group_id),
    INDEX idx_product_type (product_type),
    INDEX idx_active (is_active)
);

-- LME Prices Table
CREATE TABLE lme_prices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_group VARCHAR(50) NOT NULL,
    item_group_code VARCHAR(50) NOT NULL,
    commodity VARCHAR(50) NOT NULL,
    price DECIMAL(12,4) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    unit VARCHAR(10) DEFAULT 'MT',
    price_date DATE NOT NULL,
    source VARCHAR(100) DEFAULT 'LME',
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_lme_price (customer_group, item_group_code, price_date),
    INDEX idx_customer_group (customer_group),
    INDEX idx_commodity (commodity),
    INDEX idx_price_date (price_date),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Fabrication Costs Table
CREATE TABLE fab_costs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_group VARCHAR(50) NOT NULL,
    cost_type VARCHAR(100) NOT NULL,
    cost_value DECIMAL(12,4) NOT NULL,
    unit VARCHAR(10) NOT NULL,
    description TEXT,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_customer_group (customer_group),
    INDEX idx_cost_type (cost_type),
    INDEX idx_effective_dates (effective_from, effective_to),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Standard Prices Table
CREATE TABLE standard_prices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rm_code VARCHAR(50) NOT NULL,
    customer_group VARCHAR(50) NOT NULL,
    price DECIMAL(12,4) NOT NULL,
    unit VARCHAR(10) NOT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    currency VARCHAR(3) DEFAULT 'THB',
    supplier VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_standard_price (rm_code, customer_group, effective_from),
    INDEX idx_rm_code (rm_code),
    INDEX idx_customer_group (customer_group),
    INDEX idx_effective_dates (effective_from, effective_to),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Selling Factors Table
CREATE TABLE selling_factors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pattern VARCHAR(50) UNIQUE NOT NULL,
    factor DECIMAL(8,4) NOT NULL,
    description TEXT,
    min_quantity INT DEFAULT 0,
    max_quantity INT DEFAULT NULL,
    effective_from DATE NOT NULL,
    effective_to DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_pattern (pattern),
    INDEX idx_effective_dates (effective_from, effective_to),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Exchange Rates Table
CREATE TABLE exchange_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_group VARCHAR(50) NOT NULL,
    currency_pair VARCHAR(10) NOT NULL,
    rate DECIMAL(12,6) NOT NULL,
    rate_date DATE NOT NULL,
    source VARCHAR(100) DEFAULT 'MANUAL',
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_exchange_rate (customer_group, currency_pair, rate_date),
    INDEX idx_customer_group (customer_group),
    INDEX idx_currency_pair (currency_pair),
    INDEX idx_rate_date (rate_date),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Item Group Mappings Table
CREATE TABLE item_group_mappings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_group_code VARCHAR(50) UNIQUE NOT NULL,
    group_name VARCHAR(255),
    calculation_type ENUM('Main', 'Other') NOT NULL DEFAULT 'Other',
    description TEXT,
    pricing_method ENUM('LME', 'Standard', 'Custom') DEFAULT 'Standard',
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_item_group_code (item_group_code),
    INDEX idx_calculation_type (calculation_type),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =======================================
-- 3. PRICING & CALCULATION TABLES
-- =======================================

-- Price Requests Table
CREATE TABLE price_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id VARCHAR(50) NOT NULL,
    fg_code VARCHAR(50) NOT NULL,
    drawing_no VARCHAR(100),
    part_name VARCHAR(255),
    model VARCHAR(100),
    pattern VARCHAR(50),
    quantity INT NOT NULL,
    unit_price DECIMAL(15,4),
    total_price DECIMAL(15,2),
    status ENUM('Draft', 'Calculated', 'Quoted', 'Approved', 'Rejected') DEFAULT 'Draft',
    calculation_data JSON,
    notes TEXT,
    quoted_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED,
    approved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_request_number (request_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_fg_code (fg_code),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Temporary Formulas Header Table
CREATE TABLE temporary_formulas_header (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    header_number VARCHAR(50) UNIQUE NOT NULL,
    fg_code VARCHAR(50) NOT NULL,
    fg_name VARCHAR(255),
    pattern VARCHAR(50),
    customer_group VARCHAR(50),
    status ENUM('Pending', 'Approved', 'Rejected', 'Synced') DEFAULT 'Pending',
    total_cost DECIMAL(15,4) DEFAULT 0,
    notes TEXT,
    approved_at TIMESTAMP NULL,
    synced_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED,
    approved_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_fg_code (fg_code),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Temporary Formulas Lines Table
CREATE TABLE temporary_formulas_lines (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    header_id BIGINT UNSIGNED NOT NULL,
    line_number INT NOT NULL,
    rm_code VARCHAR(50) NOT NULL,
    rm_name VARCHAR(255),
    item_group_code VARCHAR(50),
    quantity DECIMAL(12,6) NOT NULL,
    unit VARCHAR(10) NOT NULL,
    unit_cost DECIMAL(12,4) DEFAULT 0,
    total_cost DECIMAL(15,4) DEFAULT 0,
    material_type ENUM('Main', 'Other') DEFAULT 'Other',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_formula_line (header_id, line_number),
    INDEX idx_header_id (header_id),
    INDEX idx_rm_code (rm_code),
    INDEX idx_material_type (material_type),
    
    FOREIGN KEY (header_id) REFERENCES temporary_formulas_header(id) ON DELETE CASCADE
);

-- =======================================
-- 4. EMAIL & COMMUNICATION TABLES
-- =======================================

-- Email Templates Table
CREATE TABLE email_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) UNIQUE NOT NULL,
    template_type ENUM('Quote', 'Notification', 'Approval', 'Rejection') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT,
    variables JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_template_type (template_type),
    INDEX idx_active (is_active),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Email Queue Table
CREATE TABLE email_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    to_email VARCHAR(255) NOT NULL,
    cc_email VARCHAR(255),
    bcc_email VARCHAR(255),
    subject VARCHAR(255) NOT NULL,
    body_html TEXT,
    body_text TEXT,
    attachments JSON,
    priority ENUM('High', 'Normal', 'Low') DEFAULT 'Normal',
    status ENUM('Pending', 'Sending', 'Sent', 'Failed') DEFAULT 'Pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    scheduled_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_created_at (created_at)
);

-- =======================================
-- 5. SYSTEM & LOGGING TABLES
-- =======================================

-- API Logs Table
CREATE TABLE api_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    request_data JSON,
    response_data JSON,
    response_code INT,
    response_time INT,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_endpoint (endpoint),
    INDEX idx_method (method),
    INDEX idx_response_code (response_code),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- System Settings Table
CREATE TABLE system_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Data Sync Logs Table
CREATE TABLE sync_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sync_type ENUM('Products', 'Customers', 'BOM', 'Manual') NOT NULL,
    entity_type VARCHAR(50),
    entity_id VARCHAR(50),
    action ENUM('CREATE', 'UPDATE', 'DELETE', 'SYNC') NOT NULL,
    status ENUM('Success', 'Failed', 'Partial') NOT NULL,
    records_processed INT DEFAULT 0,
    records_success INT DEFAULT 0,
    records_failed INT DEFAULT 0,
    error_details JSON,
    sync_duration INT,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_sync_type (sync_type),
    INDEX idx_entity_type (entity_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =======================================
-- 6. INSERT SAMPLE DATA
-- =======================================

-- Insert Default Admin User
INSERT INTO users (name, email, password, role, department, is_active) VALUES 
('System Admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'IT', TRUE),
('Sales Manager', 'sales.manager@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager', 'Sales', TRUE),
('John Sales', 'john.sales@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sales', 'Sales', TRUE);

-- Insert Sample Customers
INSERT INTO customers (customer_id, customer_name, customer_group, email, phone, is_active) VALUES
('CUST-A001', 'บริษัท เอ็บซี จำกัด (มหาชน)', 'กลุ่ม A', 'purchasing@abc.co.th', '02-123-4567', TRUE),
('CUST-B001', 'บริษัท ซีดี จำกัด', 'กลุ่ม B', 'buyer@cd.co.th', '02-234-5678', TRUE),
('CUST-C001', 'บริษัท อีเอฟ อินดัสทรี่ จำกัด', 'กลุ่ม C', 'procurement@ef.co.th', '02-345-6789', TRUE);

-- Insert Sample Products
INSERT INTO products (item_id, item_name, item_group_id, unit_id, product_type, is_active) VALUES
('FG-001', 'แผ่นโลหะฐานเครื่องจักรขนาดใหญ่', 'FINISHED', 'PCS', 'FG', TRUE),
('FG-002', 'ชิ้นส่วนโลหะสำหรับยานยนต์', 'FINISHED', 'PCS', 'FG', TRUE),
('RM-CU-001', 'Copper Sheet 99.9%', 'COPPER', 'KG', 'RM', TRUE),
('RM-AL-001', 'Aluminum Bar 6061-T6', 'ALUMINUM', 'KG', 'RM', TRUE),
('RM-SC-001', 'Stainless Steel Screw M6x20', 'SCREW', 'PCS', 'RM', TRUE),
('RM-WS-001', 'Flat Washer M6', 'WASHER', 'PCS', 'RM', TRUE);

-- Insert Item Group Mappings
INSERT INTO item_group_mappings (item_group_code, group_name, calculation_type, pricing_method, is_active) VALUES
('COPPER', 'โลหะทองแดง', 'Main', 'LME', TRUE),
('ALUMINUM', 'โลหะอลูมิเนียม', 'Main', 'LME', TRUE),
('STEEL', 'เหล็กกล้า', 'Main', 'LME', TRUE),
('SCREW', 'สกรูและน็อต', 'Other', 'Standard', TRUE),
('WASHER', 'แหวนรองและปะเก็น', 'Other', 'Standard', TRUE),
('PAINT', 'สีและเคมีภัณฑ์', 'Other', 'Standard', TRUE);

-- Insert LME Prices
INSERT INTO lme_prices (customer_group, item_group_code, commodity, price, currency, unit, price_date, created_by) VALUES
('กลุ่ม A', 'COPPER', 'Copper', 8245.50, 'USD', 'MT', CURDATE(), 1),
('กลุ่ม A', 'ALUMINUM', 'Aluminum', 2156.75, 'USD', 'MT', CURDATE(), 1),
('กลุ่ม B', 'COPPER', 'Copper', 8195.25, 'USD', 'MT', CURDATE(), 1),
('กลุ่ม B', 'ALUMINUM', 'Aluminum', 2125.50, 'USD', 'MT', CURDATE(), 1),
('กลุ่ม C', 'COPPER', 'Copper', 8150.00, 'USD', 'MT', CURDATE(), 1),
('กลุ่ม C', 'ALUMINUM', 'Aluminum', 2100.25, 'USD', 'MT', CURDATE(), 1);

-- Insert Fabrication Costs
INSERT INTO fab_costs (customer_group, cost_type, cost_value, unit, description, effective_from, created_by) VALUES
('กลุ่ม A', 'การตัด (Cutting)', 150.00, 'PCS', 'ตัดด้วย Laser', CURDATE(), 1),
('กลุ่ม A', 'การเชื่อม (Welding)', 200.00, 'M', 'เชื่อม TIG', CURDATE(), 1),
('กลุ่ม A', 'การขึ้นรูป (Forming)', 120.00, 'PCS', 'ขึ้นรูปด้วย Press', CURDATE(), 1),
('กลุ่ม B', 'การตัด (Cutting)', 140.00, 'PCS', 'ตัดด้วย Plasma', CURDATE(), 1),
('กลุ่ม B', 'การเชื่อม (Welding)', 180.00, 'M', 'เชื่อม MIG', CURDATE(), 1),
('กลุ่ม C', 'การประกอบ (Assembly)', 180.00, 'SET', 'ประกอบชิ้นส่วน', CURDATE(), 1);

-- Insert Standard Prices
INSERT INTO standard_prices (rm_code, customer_group, price, unit, effective_from, currency, created_by) VALUES
('RM-SC-001', 'กลุ่ม A', 15.50, 'PCS', CURDATE(), 'THB', 1),
('RM-SC-001', 'กลุ่ม B', 16.25, 'PCS', CURDATE(), 'THB', 1),
('RM-WS-001', 'กลุ่ม A', 2.25, 'PCS', CURDATE(), 'THB', 1),
('RM-WS-001', 'กลุ่ม B', 2.50, 'PCS', CURDATE(), 'THB', 1);

-- Insert Selling Factors
INSERT INTO selling_factors (pattern, factor, description, effective_from, created_by) VALUES
('Standard', 1.25, 'การผลิตมาตรฐาน', CURDATE(), 1),
('Custom', 1.45, 'การผลิตตามสั่ง', CURDATE(), 1),
('Premium', 1.60, 'งานพิเศษคุณภาพสูง', CURDATE(), 1),
('Rush', 1.80, 'งานเร่งด่วน', CURDATE(), 1);

-- Insert Exchange Rates
INSERT INTO exchange_rates (customer_group, currency_pair, rate, rate_date, source, created_by) VALUES
('กลุ่ม A', 'USD/THB', 35.42, CURDATE(), 'Bank of Thailand', 1),
('กลุ่ม A', 'EUR/THB', 38.75, CURDATE(), 'Bank of Thailand', 1),
('กลุ่ม A', 'JPY/THB', 0.24, CURDATE(), 'Bank of Thailand', 1),
('กลุ่ม B', 'USD/THB', 35.50, CURDATE(), 'Commercial Bank', 1),
('กลุ่ม B', 'EUR/THB', 38.90, CURDATE(), 'Commercial Bank', 1),
('กลุ่ม C', 'USD/THB', 35.60, CURDATE(), 'Market Rate', 1);

-- Insert Email Templates
INSERT INTO email_templates (template_name, template_type, subject, body_html, variables, created_by) VALUES
('standard-quote', 'Quote', 'ใบเสนอราคาจาก {{COMPANY_NAME}}', 
'<h2>ใบเสนอราคา</h2><p>เรียน คุณ {{CUSTOMER_NAME}}</p><p>ตามที่ท่านได้สอบถามราคาสินค้า {{PRODUCT_NAME}} จำนวน {{QUANTITY}} {{UNIT}}</p><p>ราคาที่เสนอ: <strong>{{TOTAL_PRICE}} บาท</strong></p><p>ใบเสนอราคานี้มีอายุ 30 วัน</p>', 
'["COMPANY_NAME", "CUSTOMER_NAME", "PRODUCT_NAME", "QUANTITY", "UNIT", "TOTAL_PRICE"]', 1);

-- Insert System Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public, created_by) VALUES
('company_name', 'บริษัท ตัวอย่าง จำกัด', 'string', 'ชื่อบริษัท', TRUE, 1),
('default_currency', 'THB', 'string', 'สกุลเงินหลัก', TRUE, 1),
('quote_validity_days', '30', 'integer', 'อายุใบเสนอราคา (วัน)', TRUE, 1),
('max_file_upload_size', '10', 'integer', 'ขนาดไฟล์สูงสุด (MB)', FALSE, 1),
('email_notifications_enabled', 'true', 'boolean', 'เปิดใช้การแจ้งเตือนทางอีเมล', TRUE, 1);

-- =======================================
-- 7. CREATE VIEWS FOR REPORTING
-- =======================================

-- View: Latest LME Prices
CREATE VIEW v_latest_lme_prices AS
SELECT 
    lp.customer_group,
    lp.item_group_code,
    lp.commodity,
    lp.price,
    lp.currency,
    lp.unit,
    lp.price_date,
    er.rate as exchange_rate,
    ROUND(lp.price * er.rate / 1000, 4) as price_thb_per_kg
FROM lme_prices lp
LEFT JOIN exchange_rates er ON lp.customer_group = er.customer_group 
    AND er.currency_pair = CONCAT(lp.currency, '/THB')
    AND er.rate_date = (SELECT MAX(rate_date) FROM exchange_rates er2 
                        WHERE er2.customer_group = er.customer_group 
                        AND er2.currency_pair = er.currency_pair)
WHERE lp.price_date = (SELECT MAX(price_date) FROM lme_prices lp2 
                       WHERE lp2.customer_group = lp.customer_group 
                       AND lp2.item_group_code = lp.item_group_code)
AND lp.is_active = TRUE;

-- View: Active Standard Prices
CREATE VIEW v_active_standard_prices AS
SELECT 
    sp.rm_code,
    p.item_name as rm_name,
    sp.customer_group,
    sp.price,
    sp.unit,
    sp.currency,
    sp.effective_from,
    sp.effective_to,
    igm.calculation_type,
    igm.pricing_method
FROM standard_prices sp
LEFT JOIN products p ON sp.rm_code = p.item_id
LEFT JOIN item_group_mappings igm ON p.item_group_id = igm.item_group_code
WHERE sp.is_active = TRUE
AND sp.effective_from <= CURDATE()
AND (sp.effective_to IS NULL OR sp.effective_to >= CURDATE());

-- View: Pricing Summary Report
CREATE VIEW v_pricing_summary AS
SELECT 
    pr.request_number,
    pr.customer_id,
    c.customer_name,
    pr.fg_code,
    p.item_name as fg_name,
    pr.quantity,
    pr.unit_price,
    pr.total_price,
    pr.status,
    pr.pattern,
    u.name as created_by_name,
    pr.created_at,
    pr.quoted_at
FROM price_requests pr
LEFT JOIN customers c ON pr.customer_id = c.customer_id
LEFT JOIN products p ON pr.fg_code = p.item_id
LEFT JOIN users u ON pr.created_by = u.id
ORDER BY pr.created_at DESC;

COMMIT;

-- =======================================
-- 8. PERFORMANCE OPTIMIZATION
-- =======================================

-- Additional Indexes for Performance
CREATE INDEX idx_lme_prices_lookup ON lme_prices(customer_group, item_group_code, price_date DESC);
CREATE INDEX idx_standard_prices_lookup ON standard_prices(rm_code, customer_group, effective_from DESC);
CREATE INDEX idx_exchange_rates_lookup ON exchange_rates(customer_group, currency_pair, rate_date DESC);
CREATE INDEX idx_price_requests_status_date ON price_requests(status, created_at DESC);

-- =======================================
-- 9. STORED PROCEDURES FOR BUSINESS LOGIC
-- =======================================

DELIMITER //

-- Procedure: Calculate Material Cost
CREATE PROCEDURE sp_calculate_material_cost(
    IN p_customer_group VARCHAR(50),
    IN p_rm_code VARCHAR(50),
    IN p_quantity DECIMAL(12,6),
    IN p_calculation_date DATE,
    OUT p_unit_cost DECIMAL(12,4),
    OUT p_total_cost DECIMAL(15,4),
    OUT p_cost_type VARCHAR(20)
)
BEGIN
    DECLARE v_item_group VARCHAR(50);
    DECLARE v_calculation_type VARCHAR(10);
    DECLARE v_lme_price DECIMAL(12,4);
    DECLARE v_exchange_rate DECIMAL(12,6);
    DECLARE v_standard_price DECIMAL(12,4);
    
    -- Get item group and calculation type
    SELECT p.item_group_id INTO v_item_group 
    FROM products p WHERE p.item_id = p_rm_code;
    
    SELECT igm.calculation_type INTO v_calculation_type
    FROM item_group_mappings igm WHERE igm.item_group_code = v_item_group;
    
    IF v_calculation_type = 'Main' THEN
        -- Use LME pricing
        SELECT lp.price INTO v_lme_price
        FROM lme_prices lp
        WHERE lp.customer_group = p_customer_group
        AND lp.item_group_code = v_item_group
        AND lp.price_date <= p_calculation_date
        ORDER BY lp.price_date DESC
        LIMIT 1;
        
        -- Get exchange rate
        SELECT er.rate INTO v_exchange_rate
        FROM exchange_rates er
        WHERE er.customer_group = p_customer_group
        AND er.currency_pair = 'USD/THB'
        AND er.rate_date <= p_calculation_date
        ORDER BY er.rate_date DESC
        LIMIT 1;
        
        SET p_unit_cost = (v_lme_price * v_exchange_rate) / 1000; -- Convert MT to KG
        SET p_cost_type = 'LME';
        
    ELSE
        -- Use standard pricing
        SELECT sp.price INTO v_standard_price
        FROM standard_prices sp
        WHERE sp.rm_code = p_rm_code
        AND sp.customer_group = p_customer_group
        AND sp.effective_from <= p_calculation_date
        AND (sp.effective_to IS NULL OR sp.effective_to >= p_calculation_date)
        ORDER BY sp.effective_from DESC
        LIMIT 1;
        
        SET p_unit_cost = IFNULL(v_standard_price, 0);
        SET p_cost_type = 'Standard';
    END IF;
    
    SET p_total_cost = p_unit_cost * p_quantity;
END //

-- Procedure: Generate Request Number
CREATE PROCEDURE sp_generate_request_number(
    OUT p_request_number VARCHAR(50)
)
BEGIN
    DECLARE v_year CHAR(4);
    DECLARE v_month CHAR(2);
    DECLARE v_sequence INT;
    
    SET v_year = YEAR(CURDATE());
    SET v_month = LPAD(MONTH(CURDATE()), 2, '0');
    
    SELECT IFNULL(MAX(CAST(SUBSTRING(request_number, 12) AS UNSIGNED)), 0) + 1
    INTO v_sequence
    FROM price_requests
    WHERE request_number LIKE CONCAT('REQ-', v_year, v_month, '-%');
    
    SET p_request_number = CONCAT('REQ-', v_year, v_month, '-', LPAD(v_sequence, 4, '0'));
END //

-- Procedure: Update Pricing History
CREATE PROCEDURE sp_update_pricing_history(
    IN p_request_id BIGINT UNSIGNED,
    IN p_unit_price DECIMAL(15,4),
    IN p_total_price DECIMAL(15,2),
    IN p_calculation_data JSON
)
BEGIN
    UPDATE price_requests 
    SET 
        unit_price = p_unit_price,
        total_price = p_total_price,
        calculation_data = p_calculation_data,
        status = 'Calculated',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_request_id;
END //

-- Function: Get Latest Exchange Rate
CREATE FUNCTION fn_get_exchange_rate(
    p_customer_group VARCHAR(50),
    p_currency_pair VARCHAR(10),
    p_date DATE
) RETURNS DECIMAL(12,6)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_rate DECIMAL(12,6) DEFAULT 1.0;
    
    SELECT rate INTO v_rate
    FROM exchange_rates
    WHERE customer_group = p_customer_group
    AND currency_pair = p_currency_pair
    AND rate_date <= p_date
    AND is_active = TRUE
    ORDER BY rate_date DESC
    LIMIT 1;
    
    RETURN IFNULL(v_rate, 1.0);
END //

-- Function: Get Active Selling Factor
CREATE FUNCTION fn_get_selling_factor(
    p_pattern VARCHAR(50),
    p_date DATE
) RETURNS DECIMAL(8,4)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_factor DECIMAL(8,4) DEFAULT 1.25;
    
    SELECT factor INTO v_factor
    FROM selling_factors
    WHERE pattern = p_pattern
    AND effective_from <= p_date
    AND (effective_to IS NULL OR effective_to >= p_date)
    AND is_active = TRUE
    ORDER BY effective_from DESC
    LIMIT 1;
    
    RETURN IFNULL(v_factor, 1.25);
END //

DELIMITER ;

-- =======================================
-- 10. TRIGGERS FOR AUDIT AND AUTOMATION
-- =======================================

DELIMITER //

-- Trigger: Auto-generate request number
CREATE TRIGGER tr_price_requests_before_insert
BEFORE INSERT ON price_requests
FOR EACH ROW
BEGIN
    IF NEW.request_number IS NULL OR NEW.request_number = '' THEN
        CALL sp_generate_request_number(@new_number);
        SET NEW.request_number = @new_number;
    END IF;
END //

-- Trigger: Update calculation data when status changes
CREATE TRIGGER tr_price_requests_after_update
AFTER UPDATE ON price_requests
FOR EACH ROW
BEGIN
    IF NEW.status = 'Quoted' AND OLD.status != 'Quoted' THEN
        SET NEW.quoted_at = CURRENT_TIMESTAMP;
    END IF;
END //

-- Trigger: Log LME price changes
CREATE TRIGGER tr_lme_prices_after_insert
AFTER INSERT ON lme_prices
FOR EACH ROW
BEGIN
    INSERT INTO sync_logs (sync_type, entity_type, entity_id, action, status, records_processed, records_success, created_by)
    VALUES ('Manual', 'LME_PRICE', CONCAT(NEW.customer_group, '-', NEW.item_group_code), 'CREATE', 'Success', 1, 1, NEW.created_by);
END //

-- Trigger: Validate exchange rate ranges
CREATE TRIGGER tr_exchange_rates_before_insert
BEFORE INSERT ON exchange_rates
FOR EACH ROW
BEGIN
    IF NEW.rate <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Exchange rate must be positive';
    END IF;
    
    IF NEW.currency_pair LIKE '%/THB' AND NEW.rate > 100 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Exchange rate seems unrealistic';
    END IF;
END //

-- Trigger: Auto-expire old price requests
CREATE TRIGGER tr_price_requests_before_select
BEFORE INSERT ON price_requests
FOR EACH ROW
BEGIN
    -- Set expiration date to 30 days from creation
    IF NEW.expires_at IS NULL THEN
        SET NEW.expires_at = DATE_ADD(NEW.created_at, INTERVAL 30 DAY);
    END IF;
END //

DELIMITER ;

-- =======================================
-- 11. SAMPLE COMPLEX QUERIES FOR TESTING
-- =======================================

-- Query: Get complete pricing calculation data
SELECT 
    pr.request_number,
    pr.customer_id,
    c.customer_name,
    pr.fg_code,
    p.item_name as product_name,
    pr.quantity,
    pr.unit_price,
    pr.total_price,
    pr.pattern,
    sf.factor as selling_factor,
    JSON_UNQUOTE(JSON_EXTRACT(pr.calculation_data, '$.breakdown.mainMaterialCost')) as main_material_cost,
    JSON_UNQUOTE(JSON_EXTRACT(pr.calculation_data, '$.breakdown.otherMaterialCost')) as other_material_cost,
    JSON_UNQUOTE(JSON_EXTRACT(pr.calculation_data, '$.breakdown.fabricationCost')) as fabrication_cost,
    pr.status,
    pr.created_at,
    u.name as sales_person
FROM price_requests pr
LEFT JOIN customers c ON pr.customer_id = c.customer_id
LEFT JOIN products p ON pr.fg_code = p.item_id
LEFT JOIN selling_factors sf ON pr.pattern = sf.pattern
LEFT JOIN users u ON pr.created_by = u.id
WHERE pr.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY pr.created_at DESC;

-- Query: Material cost analysis
SELECT 
    p.item_id,
    p.item_name,
    igm.calculation_type,
    CASE 
        WHEN igm.calculation_type = 'Main' THEN vlp.price_thb_per_kg
        ELSE vasp.price
    END as current_price,
    CASE 
        WHEN igm.calculation_type = 'Main' THEN 'THB/KG (from LME)'
        ELSE CONCAT('THB/', vasp.unit, ' (Standard)')
    END as price_type,
    COUNT(DISTINCT pr.id) as usage_in_requests
FROM products p
JOIN item_group_mappings igm ON p.item_group_id = igm.item_group_code
LEFT JOIN v_latest_lme_prices vlp ON igm.item_group_code = vlp.item_group_code
LEFT JOIN v_active_standard_prices vasp ON p.item_id = vasp.rm_code
LEFT JOIN price_requests pr ON JSON_SEARCH(pr.calculation_data, 'one', p.item_id) IS NOT NULL
WHERE p.product_type = 'RM'
AND p.is_active = TRUE
GROUP BY p.item_id, p.item_name, igm.calculation_type
ORDER BY usage_in_requests DESC, p.item_name;

-- Query: Customer pricing trends
SELECT 
    c.customer_id,
    c.customer_name,
    c.customer_group,
    COUNT(pr.id) as total_requests,
    AVG(pr.unit_price) as avg_unit_price,
    SUM(pr.total_price) as total_quoted_value,
    COUNT(CASE WHEN pr.status = 'Approved' THEN 1 END) as approved_quotes,
    ROUND(COUNT(CASE WHEN pr.status = 'Approved' THEN 1 END) * 100.0 / COUNT(pr.id), 2) as approval_rate
FROM customers c
LEFT JOIN price_requests pr ON c.customer_id = pr.customer_id
WHERE c.is_active = TRUE
AND (pr.created_at IS NULL OR pr.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY))
GROUP BY c.customer_id, c.customer_name, c.customer_group
HAVING total_requests > 0 OR c.customer_id IN (SELECT DISTINCT customer_id FROM price_requests WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY))
ORDER BY total_quoted_value DESC;

-- =======================================
-- 12. DATA CLEANUP AND MAINTENANCE
-- =======================================

-- Create maintenance procedures
DELIMITER //

-- Procedure: Clean old logs
CREATE PROCEDURE sp_cleanup_old_logs(IN p_days_to_keep INT DEFAULT 90)
BEGIN
    DELETE FROM api_logs WHERE created_at < DATE_SUB(CURDATE(), INTERVAL p_days_to_keep DAY);
    DELETE FROM sync_logs WHERE created_at < DATE_SUB(CURDATE(), INTERVAL p_days_to_keep DAY);
    DELETE FROM email_queue WHERE status = 'Sent' AND created_at < DATE_SUB(CURDATE(), INTERVAL p_days_to_keep DAY);
    
    SELECT ROW_COUNT() as rows_cleaned;
END //

-- Procedure: Archive old price requests
CREATE PROCEDURE sp_archive_old_requests(IN p_days_to_keep INT DEFAULT 365)
BEGIN
    CREATE TABLE IF NOT EXISTS price_requests_archive LIKE price_requests;
    
    INSERT INTO price_requests_archive 
    SELECT * FROM price_requests 
    WHERE created_at < DATE_SUB(CURDATE(), INTERVAL p_days_to_keep DAY)
    AND status IN ('Rejected', 'Expired');
    
    DELETE FROM price_requests 
    WHERE created_at < DATE_SUB(CURDATE(), INTERVAL p_days_to_keep DAY)
    AND status IN ('Rejected', 'Expired');
    
    SELECT ROW_COUNT() as requests_archived;
END //

-- Procedure: Update expired requests
CREATE PROCEDURE sp_update_expired_requests()
BEGIN
    UPDATE price_requests 
    SET status = 'Expired' 
    WHERE status IN ('Draft', 'Calculated', 'Quoted')
    AND expires_at < CURRENT_TIMESTAMP;
    
    SELECT ROW_COUNT() as requests_expired;
END //

DELIMITER ;

-- =======================================
-- 13. FINAL OPTIMIZATIONS AND SUMMARY
-- =======================================

-- Analyze tables for optimization
ANALYZE TABLE customers, products, lme_prices, standard_prices, price_requests;

-- Create summary statistics
SELECT 
    'Database Setup Complete' as status,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'fg_pricing') as total_tables,
    (SELECT COUNT(*) FROM information_schema.views WHERE table_schema = 'fg_pricing') as total_views,
    (SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = 'fg_pricing') as total_procedures,
    (SELECT COUNT(*) FROM information_schema.triggers WHERE trigger_schema = 'fg_pricing') as total_triggers,
    CURDATE() as setup_date;

-- Show table sizes
SELECT 
    table_name,
    table_rows as estimated_rows,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) as size_mb
FROM information_schema.tables 
WHERE table_schema = 'fg_pricing' 
AND table_type = 'BASE TABLE'
ORDER BY size_mb DESC;

COMMIT;