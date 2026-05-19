-- ============================================================
-- TCC Inventory System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS school_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_inventory;

-- --------------------------------------------------------
-- Roles
-- --------------------------------------------------------
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Users
-- --------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(30),
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Departments
-- --------------------------------------------------------
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Categories
-- --------------------------------------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Suppliers
-- --------------------------------------------------------
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(30),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Inventory Items
-- --------------------------------------------------------
CREATE TABLE inventory_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category_id INT,
    serial_number VARCHAR(100) UNIQUE,
    asset_tag VARCHAR(50) UNIQUE,
    quantity INT DEFAULT 0,
    unit VARCHAR(30) DEFAULT 'pcs',
    condition_status ENUM('new', 'good', 'fair', 'poor', 'damaged') DEFAULT 'good',
    location VARCHAR(100),
    department_id INT,
    purchase_date DATE,
    supplier_id INT,
    status ENUM('available', 'in_use', 'maintenance', 'retired') DEFAULT 'available',
    description TEXT,
    image_path VARCHAR(255),
    reorder_level INT DEFAULT 5,
    unit_price DECIMAL(10,2) DEFAULT 0.00,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Borrow Requests
-- --------------------------------------------------------
CREATE TABLE borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    purpose TEXT NOT NULL,
    expected_return_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'returned', 'overdue') DEFAULT 'pending',
    approved_by INT,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Borrow History
-- --------------------------------------------------------
CREATE TABLE borrow_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date TIMESTAMP NULL,
    condition_out ENUM('new', 'good', 'fair', 'poor', 'damaged'),
    condition_in ENUM('new', 'good', 'fair', 'poor', 'damaged'),
    remarks TEXT,
    FOREIGN KEY (request_id) REFERENCES borrow_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Returns
-- --------------------------------------------------------
CREATE TABLE returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    borrow_history_id INT NOT NULL,
    processed_by INT NOT NULL,
    return_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    condition_received ENUM('new', 'good', 'fair', 'poor', 'damaged'),
    penalty_fee DECIMAL(10,2) DEFAULT 0.00,
    remarks TEXT,
    FOREIGN KEY (borrow_history_id) REFERENCES borrow_history(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Damaged Items
-- --------------------------------------------------------
CREATE TABLE damaged_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    reported_by INT NOT NULL,
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    damage_description TEXT NOT NULL,
    repair_cost DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('reported', 'repairing', 'fixed', 'disposed') DEFAULT 'reported',
    resolved_date TIMESTAMP NULL,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Stock Movements
-- --------------------------------------------------------
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    movement_type ENUM('in', 'out', 'adjustment') NOT NULL,
    quantity INT NOT NULL,
    reference_number VARCHAR(100),
    remarks TEXT,
    performed_by INT NOT NULL,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Activity Logs
-- --------------------------------------------------------
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Notifications
-- --------------------------------------------------------
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('alert', 'info', 'success', 'warning') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- Settings
-- --------------------------------------------------------
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Seed Data
-- ============================================================

INSERT INTO roles (id, name, description) VALUES
(1, 'Admin', 'Full system access'),
(2, 'Warehouse Manager', 'Manage inventory and approvals'),
(3, 'Staff', 'View inventory and request borrowing');

INSERT INTO users (role_id, username, password, full_name, email, status) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@school.edu', 'active'),
(2, 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Warehouse Manager', 'manager@school.edu', 'active'),
(3, 'staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Regular Staff', 'staff@school.edu', 'active');
-- Password for all is: password

INSERT INTO departments (id, name, description) VALUES
(1, 'IT Department', 'Information Technology and Computer Labs'),
(2, 'Science Department', 'Science Laboratories and Equipment'),
(3, 'Physical Education', 'Sports and PE Facilities'),
(4, 'Administration', 'Main Office and Admin Rooms');

INSERT INTO categories (id, name, description) VALUES
(1, 'Computers', 'Desktops, laptops, and servers'),
(2, 'Furniture', 'Desks, chairs, tables, cabinets'),
(3, 'Lab Equipment', 'Microscopes, beakers, test tubes'),
(4, 'Electronics', 'Projectors, monitors, audio systems'),
(5, 'Office Supplies', 'Consumables, paper, pens');

INSERT INTO suppliers (id, name, contact_person, email, phone, address) VALUES
(1, 'TechSource IT Solutions', 'John Doe', 'john@techsource.com', '0917-123-4567', 'Makati City'),
(2, 'EduFurnishings Corp', 'Jane Smith', 'jane@edufurnish.com', '02-8888-9999', 'Quezon City'),
(3, 'LabMaster Inc', 'Dr. Lee', 'sales@labmaster.ph', '0922-333-4444', 'Manila');

INSERT INTO inventory_items (id, name, category_id, serial_number, asset_tag, quantity, unit, condition_status, location, department_id, purchase_date, supplier_id, status, reorder_level, unit_price, created_by) VALUES
(1, 'Dell OptiPlex 7090 Desktop', 1, 'SN-DELL-001', 'TAG-001', 25, 'pcs', 'new', 'Computer Lab A', 1, '2023-08-15', 1, 'available', 5, 45000.00, 1),
(2, 'Student Armchair (Wood/Metal)', 2, NULL, 'TAG-002', 150, 'pcs', 'good', 'Room 101-105', 4, '2022-05-10', 2, 'available', 20, 1500.00, 1),
(3, 'Binocular Microscope', 3, 'SN-MIC-045', 'TAG-003', 10, 'pcs', 'good', 'Science Lab 1', 2, '2021-11-20', 3, 'available', 2, 12500.00, 1),
(4, 'Epson EB-X51 Projector', 4, 'SN-EPS-992', 'TAG-004', 5, 'pcs', 'good', 'AVR Room', 1, '2023-01-12', 1, 'available', 1, 18000.00, 1),
(5, 'A4 Bond Paper (500 sheets)', 5, NULL, NULL, 50, 'ream', 'new', 'Supply Room', 4, '2024-01-05', 2, 'available', 15, 250.00, 1);

INSERT INTO settings (setting_key, setting_value, description) VALUES
('school_name', 'TCC', 'Name of the institution'),
('system_email', 'inventory@school.edu', 'System outbound email'),
('currency', 'PHP', 'Default currency'),
('allow_staff_requests', '1', 'Allow staff to make borrow requests');
