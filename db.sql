CREATE DATABASE IF NOT EXISTS depodadepo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE depodadepo;

-- Adminler
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Depolar
CREATE TABLE depots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,          -- Örn: A101
    block CHAR(1) NOT NULL,                    -- A / B / C / D
    number INT NOT NULL,                       -- 101..120
    size_m2 INT NOT NULL,                      -- 20 / 30 / 35
    status ENUM('available','occupied') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
USE depodadepo;
-- 80 depoyu otomatik ekle (A–D, 101–120)
INSERT INTO depots (code, block, number, size_m2)
SELECT 
  CONCAT(blocks.b, nums.n) AS code,
  blocks.b AS block,
  nums.n AS number,
  CASE 
    WHEN nums.n BETWEEN 101 AND 108 THEN 20
    WHEN nums.n BETWEEN 109 AND 115 THEN 30
    ELSE 35
  END AS size_m2
FROM 
  (SELECT 'A' AS b UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D') AS blocks,
  (SELECT 101 AS n UNION SELECT 102 UNION SELECT 103 UNION SELECT 104 UNION SELECT 105 UNION SELECT 106 UNION
          SELECT 107 UNION SELECT 108 UNION SELECT 109 UNION SELECT 110 UNION SELECT 111 UNION SELECT 112 UNION
          SELECT 113 UNION SELECT 114 UNION SELECT 115 UNION SELECT 116 UNION SELECT 117 UNION SELECT 118 UNION
          SELECT 119 UNION SELECT 120) AS nums;

-- Müşteriler
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    email VARCHAR(150),
    address TEXT,
    note TEXT,
    authorized_name VARCHAR(150),
    authorized_phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kiralamalar
CREATE TABLE rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    depot_id INT NOT NULL,
    customer_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,                        -- NULL ise süresiz
    three_digit_code CHAR(3) NOT NULL,
    status ENUM('active','passive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (depot_id) REFERENCES depots(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Örnek bir admin kullanıcısı: admin@test.com / 123456
INSERT INTO admins (name,email,password_hash)
VALUES ('Sistem Admin','admin@test.com',
        '$2y$10$eQHc.25C48X9u41RMm4D8uPFQ9ig7o9sZC7JmZpylG9UIxPIPaWZe');
-- Şifre: 123456  (bcrypt hash)