-- ============================================================
-- Order Tracking System — Sample Data
-- Run AFTER schema.sql
-- ============================================================

USE order_tracking_db;

-- ─────────────────────────────────────────────
-- BRANCHES
-- ─────────────────────────────────────────────
INSERT INTO branches (branch_code, branch_name, street, city, state, zip_code, country, branch_phone) VALUES
('dIbUK5mEh96f0Zc', 'Charlotte Branch',  'Lindbergh',      'Charlotte',  'NC',      '123456', 'USA',                      '123456'),
('ZAisL9RlMHF12We', 'Atlanta Branch 1',  'Lindbergh2',     'Atlanta',    'Georgia', '2002',   'USA',                      '123456789'),
('Kylab3mYBgAX71t', 'Atlanta Midtown',   'Midtown',        'Atlanta',    'Georgia', '6000',   'USA',                      '+1234567489'),
('vzTL0PqMogyOWhF', 'Peachtree Branch',  'Peachtree',      'Atlanta',    'Georgia', '30324',  'United States Of America', '+1 234 567 890'),
('CpcZlRrPqola7Hv', 'Petit Science',     'Petit Science',  'Atlanta',    'GA',      '30324',  'USA',                      '123456789');

-- ─────────────────────────────────────────────
-- USERS (password: MD5 of 'password' = 0192023a7bbd73250516f069df18b500)
-- type: 1=Admin, 2=Staff, 3=Customer
-- ─────────────────────────────────────────────
INSERT INTO users (firstname, lastname, email, password, type, branch_id) VALUES
('Administrator', '',        'admin@admin.com',      '0192023a7bbd73250516f069df18b500', 1, 0),
('Teja',          'Chalicham','teja@sample.com',     '0192023a7bbd73250516f069df18b500', 2, 1),
('Manohar',       'Kota',    'manohar@gmail.com',    '0192023a7bbd73250516f069df18b500', 2, 3),
('Sai',           'Sarvagna', 'sai@sample.com',      '0192023a7bbd73250516f069df18b500', 2, 4),
('Group12',       'DB',      'group12@sample.com',   '0192023a7bbd73250516f069df18b500', 3, 5),
('Manohar',       'Kota',    'manohar@sample.com',   '0192023a7bbd73250516f069df18b500', 2, 3),
('Mano',          'Ma',      'ma@sample.com',        '0192023a7bbd73250516f069df18b500', 3, 0);

-- ─────────────────────────────────────────────
-- STAFF
-- ─────────────────────────────────────────────
INSERT INTO staff (staff_name, email, branch_id) VALUES
('Manohar Kota',    'manohar@gmail.com',  3),
('Manohar Kota',    'manohar@sample.com', 3),
('Sai Sarvagna',    'sai@sample.com',     1),
('Teja Chalicham',  'teja@sample.com',    4);

-- ─────────────────────────────────────────────
-- CUSTOMERS
-- ─────────────────────────────────────────────
INSERT INTO customers (user_name, email, phone, address) VALUES
('Manohar Kota',   'manohar@gmail.com',  '1234567890', 'Midtown, Atlanta, Georgia, 6000, USA'),
('Teja Chalicham', 'teja@sample.com',    '0987654321', 'Peachtree, Atlanta, Georgia, 30324, USA'),
('Sai Sarvagna',   'sai@sample.com',     '1122334455', 'Lindbergh, Charlotte, NC, 123456, USA');

-- ─────────────────────────────────────────────
-- PARCELS (sample)
-- ─────────────────────────────────────────────
INSERT INTO parcels (reference_number, order_id, sender_name, sender_address, sender_phone,
    recipient_name, recipient_address, recipient_phone, dimensions, weight, parcel_type, delivery_type, price, status)
VALUES
('408982882770', 1, 'Manohar', 'Atlanta, GA', '1234567890',
 'Teja',         'Peachtree, Atlanta', '0987654321', '10x10x10 cm', 1.5, 'Medium', 'Delivery', 2500.00, 'Item Accepted by Courier'),
('514912669061', 1, 'DBS',     'Charlotte, NC', '1234567890',
 'Database',     'Midtown, Atlanta',   '1122334455', '20x15x10 cm', 3.0, 'Large',  'Delivery', 1000.00, 'Item Accepted by Courier');

-- ─────────────────────────────────────────────
-- PARCEL TRACKING
-- ─────────────────────────────────────────────
INSERT INTO parcel_tracks (parcel_id, location, status, timestamp) VALUES
(1, 'Charlotte Branch',   'Item Accepted by Courier', '2020-11-26 16:46:00'),
(1, 'Atlanta Midtown',    'Collected',                 '2020-11-27 08:53:00'),
(2, 'Peachtree Branch',   'Item Accepted by Courier', '2020-11-27 10:00:00');

-- ─────────────────────────────────────────────
-- SYSTEM SETTINGS
-- ─────────────────────────────────────────────
INSERT INTO system_settings (setting_key, setting_value) VALUES
('site_name',    'Order Tracking System'),
('site_email',   'admin@admin.com'),
('currency',     'USD');
