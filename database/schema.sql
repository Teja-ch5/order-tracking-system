-- ============================================================
-- Order Tracking System — Database Schema
-- Team 12: Manohar Kota, Bhagya Teja Chalicham, Sai Sarvagna Beeram
-- MySQL 8.0 | Normalized to 3NF
-- ============================================================

CREATE DATABASE IF NOT EXISTS order_tracking_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE order_tracking_db;

-- ─────────────────────────────────────────────
-- 1. USERS (Admin=1, Staff=2, Customer=3)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    firstname     VARCHAR(100) NOT NULL,
    lastname      VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL COMMENT 'MD5 hashed',
    phone         VARCHAR(15),
    address       TEXT,
    type          TINYINT NOT NULL DEFAULT 3 COMMENT '1=Admin, 2=Staff, 3=Customer',
    branch_id     INT DEFAULT NULL,
    date_created  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────
-- 2. BRANCHES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS branches (
    branch_id      INT AUTO_INCREMENT PRIMARY KEY,
    branch_code    VARCHAR(50) NOT NULL UNIQUE,
    branch_name    VARCHAR(100) NOT NULL,
    street         VARCHAR(150),
    city           VARCHAR(100),
    state          VARCHAR(100),
    zip_code       VARCHAR(20),
    country        VARCHAR(100),
    branch_phone   VARCHAR(15),
    date_created   DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────
-- 3. CUSTOMERS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS customers (
    customer_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    phone         VARCHAR(15),
    address       TEXT,
    date_created  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────
-- 4. STAFF
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS staff (
    staff_id      INT AUTO_INCREMENT PRIMARY KEY,
    staff_name    VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    branch_id     INT NOT NULL,
    date_created  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_staff_branch FOREIGN KEY (branch_id)
        REFERENCES branches(branch_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ─────────────────────────────────────────────
-- 5. ORDERS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
    order_id      INT AUTO_INCREMENT PRIMARY KEY,
    customer_id   INT NOT NULL,
    staff_id      INT,
    status        ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled')
                  NOT NULL DEFAULT 'Pending',
    order_date    DATE NOT NULL,
    date_created  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_customer FOREIGN KEY (customer_id)
        REFERENCES customers(customer_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_order_staff FOREIGN KEY (staff_id)
        REFERENCES staff(staff_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- ─────────────────────────────────────────────
-- 6. PARCELS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS parcels (
    parcel_id         INT AUTO_INCREMENT PRIMARY KEY,
    reference_number  VARCHAR(50) NOT NULL UNIQUE,
    order_id          INT NOT NULL,
    sender_name       VARCHAR(100) NOT NULL,
    sender_address    TEXT,
    sender_phone      VARCHAR(15),
    recipient_name    VARCHAR(100) NOT NULL,
    recipient_address TEXT,
    recipient_phone   VARCHAR(15),
    dimensions        VARCHAR(50)     COMMENT 'e.g. 10x5x3 cm',
    weight            DECIMAL(10,2)   COMMENT 'in kg',
    parcel_type       ENUM('Small', 'Medium', 'Large', 'Fragile', 'Perishable')
                      NOT NULL DEFAULT 'Medium',
    delivery_type     ENUM('Pickup', 'Delivery') NOT NULL DEFAULT 'Delivery',
    price             DECIMAL(10,2)   DEFAULT 0.00,
    branch_processed  INT,
    pickup_branch     INT,
    status            VARCHAR(100) NOT NULL DEFAULT 'Item Accepted by Courier',
    date_created      DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_parcel_order FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_parcel_branch FOREIGN KEY (branch_processed)
        REFERENCES branches(branch_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- ─────────────────────────────────────────────
-- 7. PARCEL TRACKING
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS parcel_tracks (
    track_id      INT AUTO_INCREMENT PRIMARY KEY,
    parcel_id     INT NOT NULL,
    location      VARCHAR(200),
    status        VARCHAR(100) NOT NULL,
    updated_by    INT COMMENT 'staff user_id',
    timestamp     DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_track_parcel FOREIGN KEY (parcel_id)
        REFERENCES parcels(parcel_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- ─────────────────────────────────────────────
-- 8. SHIPMENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS shipments (
    shipment_id    INT AUTO_INCREMENT PRIMARY KEY,
    order_id       INT NOT NULL,
    shipment_date  DATE NOT NULL,
    status         ENUM('In Transit', 'Delivered', 'Delayed', 'Cancelled')
                   NOT NULL DEFAULT 'In Transit',
    date_created   DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shipment_order FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ─────────────────────────────────────────────
-- 9. REPORTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reports (
    report_id     INT AUTO_INCREMENT PRIMARY KEY,
    shipment_id   INT NOT NULL,
    report_date   DATE NOT NULL,
    sender        VARCHAR(100) NOT NULL,
    recipient     VARCHAR(100) NOT NULL,
    amount        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status        ENUM('Paid', 'Pending', 'Processed') NOT NULL DEFAULT 'Pending',
    date_created  DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_report_shipment FOREIGN KEY (shipment_id)
        REFERENCES shipments(shipment_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ─────────────────────────────────────────────
-- 10. SYSTEM SETTINGS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS system_settings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    date_updated  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────────
-- INDEXES for performance
-- ─────────────────────────────────────────────
CREATE INDEX idx_orders_customer    ON orders(customer_id);
CREATE INDEX idx_parcels_order      ON parcels(order_id);
CREATE INDEX idx_parcels_reference  ON parcels(reference_number);
CREATE INDEX idx_tracks_parcel      ON parcel_tracks(parcel_id);
CREATE INDEX idx_shipments_order    ON shipments(order_id);
CREATE INDEX idx_reports_shipment   ON reports(shipment_id);
CREATE INDEX idx_staff_branch       ON staff(branch_id);
CREATE INDEX idx_users_email        ON users(email);

