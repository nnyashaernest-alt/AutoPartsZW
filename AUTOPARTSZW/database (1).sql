-- ============================================================
--  AutoPartsZW - Database
--  Nyasha Ernest Nyakamhanda | B242508B | NWE214
--  Import this in phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS autopartszw;
USE autopartszw;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(50)  NOT NULL,
    last_name    VARCHAR(50)  NOT NULL,
    email        VARCHAR(100) NOT NULL UNIQUE,
    phone        VARCHAR(20)  NOT NULL,
    password     VARCHAR(255) NOT NULL,
    account_type ENUM('customer','mechanic','admin') DEFAULT 'customer',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PARTS TABLE
CREATE TABLE IF NOT EXISTS parts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    category   VARCHAR(50)   NOT NULL,
    make       VARCHAR(50)   NOT NULL,
    model      VARCHAR(50)   NOT NULL,
    year_range VARCHAR(20)   NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    stock      INT           NOT NULL DEFAULT 0,
    icon       VARCHAR(10)   DEFAULT '🔧',
    image      VARCHAR(255)  DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ORDERS TABLE
CREATE TABLE IF NOT EXISTS orders (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    order_number   VARCHAR(20)   NOT NULL UNIQUE,
    user_id        INT           DEFAULT NULL,
    first_name     VARCHAR(50)   NOT NULL,
    last_name      VARCHAR(50)   NOT NULL,
    phone          VARCHAR(20)   NOT NULL,
    city           VARCHAR(50)   NOT NULL,
    address        VARCHAR(200)  NOT NULL,
    notes          TEXT,
    payment_method VARCHAR(20)   NOT NULL,
    payment_number VARCHAR(20)   DEFAULT '',
    subtotal       DECIMAL(10,2) NOT NULL,
    delivery_fee   DECIMAL(10,2) DEFAULT 5.00,
    discount       DECIMAL(10,2) DEFAULT 0.00,
    total          DECIMAL(10,2) NOT NULL,
    status         VARCHAR(20)   DEFAULT 'pending',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ORDER ITEMS TABLE
CREATE TABLE IF NOT EXISTS order_items (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    order_id  INT           NOT NULL,
    part_id   INT           NOT NULL,
    part_name VARCHAR(100)  NOT NULL,
    price     DECIMAL(10,2) NOT NULL,
    quantity  INT           NOT NULL DEFAULT 1,
    subtotal  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (part_id)  REFERENCES parts(id)  ON DELETE CASCADE
);

-- CART TABLE
CREATE TABLE IF NOT EXISTS cart (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT NOT NULL,
    part_id  INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE CASCADE
);

-- ADMIN ACCOUNT (password: admin123)
INSERT INTO users (first_name, last_name, email, phone, password, account_type) VALUES
('Admin', 'AutoPartsZW', 'admin@autopartszw.co.zw', '0771000000',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- SAMPLE PARTS
INSERT INTO parts (name,category,make,model,year_range,price,stock,icon,image) VALUES
('Front Brake Pads',  'Brakes',     'Toyota','Hilux',  '2015-2022', 18.50,15,'🛑','brake pads.webp'),
('Rear Brake Pads',   'Brakes',     'Toyota','Hilux',  '2015-2022', 16.00,10,'🛑','brake pads.webp'),
('Alternator 90A',    'Electrical', 'Toyota','Corolla','2010-2018', 65.00, 4,'⚡','alternator.jpg'),
('Shock Absorbers',   'Suspension', 'Mazda', 'B2500',  '2005-2012', 42.00, 7,'🔩','shock absorbers.jpg'),
('Oil Filter',        'Filters',    'Honda', 'Fit',    '2008-2020',  4.50,30,'🔧','oil filter.jpg'),
('Timing Belt Kit',   'Engine',     'Honda', 'Fit',    '2008-2014', 55.00, 2,'⚙️','timing belt kit.jpg'),
('Front Bumper',      'Body Parts', 'Toyota','Hilux',  '2016-2022',120.00, 3,'🚗','front bamper.jpg'),
('Air Filter',        'Filters',    'Nissan','Navara', '2010-2020',  8.00,20,'🔧','air filter.jpg'),
('Spark Plugs (x4)',  'Engine',     'Honda', 'Fit',    '2008-2020', 22.00, 1,'⚡','spark plugs.jpg'),
('Radiator',          'Engine',     'Toyota','Corolla','2010-2018', 95.00, 5,'⚙️','engine.jpg'),
('Brake Disc Rotor',  'Brakes',     'Mazda', 'B2500',  '2005-2012', 35.00, 8,'🛑','brake disc.jpg'),
('Side Mirror Left',  'Body Parts', 'Nissan','Navara', '2010-2020', 28.00, 6,'🚗','side mirror.jpg');
