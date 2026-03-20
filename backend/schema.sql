-- =====================================================
-- TSLGC Database Schema
-- Database: tslgc_db
-- Charset: utf8mb4 (supports Hindi/Devanagari)
-- =====================================================

CREATE DATABASE IF NOT EXISTS tslgc_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tslgc_db;

-- -----------------------------------------------------
-- admins
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(60)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('superadmin','admin') NOT NULL DEFAULT 'admin',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default super-admin (password: Admin@1234 — change immediately!)
INSERT INTO admins (username, password_hash, role)
VALUES ('admin', '$2y$12$qP.xwAFJiHsCHHYaVvhT4eMfbbmqtnXP1.kChBrN5zDoO2Vy4zBFe', 'superadmin');

-- -----------------------------------------------------
-- members
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS members (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_code   VARCHAR(20)  NOT NULL UNIQUE,         -- e.g. UH-000001
  full_name     VARCHAR(120) NOT NULL,
  mobile        VARCHAR(15)  NOT NULL UNIQUE,
  email         VARCHAR(120) NOT NULL UNIQUE,
  dob           DATE         DEFAULT NULL,
  city          VARCHAR(80)  DEFAULT NULL,
  state         VARCHAR(80)  DEFAULT NULL,
  referrer_id   INT UNSIGNED DEFAULT NULL,             -- FK → members.id
  plan          ENUM('full','installment') NOT NULL DEFAULT 'full',
  password_hash VARCHAR(255) NOT NULL,
  status        ENUM('pending','active','rejected','inactive') NOT NULL DEFAULT 'pending',
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (referrer_id) REFERENCES members(id) ON DELETE SET NULL,
  INDEX idx_mobile  (mobile),
  INDEX idx_email   (email),
  INDEX idx_referrer(referrer_id),
  INDEX idx_status  (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- franchises
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS franchises (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id       INT UNSIGNED NOT NULL,
  franchise_code  VARCHAR(20)  NOT NULL UNIQUE,       -- e.g. FR-0001
  area            VARCHAR(120) NOT NULL,               -- City / District
  type            ENUM('City Franchise','District Franchise','State Franchise') NOT NULL DEFAULT 'City Franchise',
  status          ENUM('active','pending','blocked') NOT NULL DEFAULT 'active',
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
  INDEX idx_fr_member (member_id),
  INDEX idx_fr_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- payments
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id         INT UNSIGNED NOT NULL,
  txn_ref           VARCHAR(40)  NOT NULL UNIQUE,
  amount            DECIMAL(10,2) NOT NULL,
  plan              ENUM('full','installment') NOT NULL,
  installment_no    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  installment_total TINYINT UNSIGNED NOT NULL DEFAULT 1,
  payment_method    ENUM('UPI','Bank Transfer','NEFT','RTGS','Net Banking') NOT NULL,
  upi_ref           VARCHAR(120) DEFAULT NULL,
  status            ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
  INDEX idx_pay_member (member_id),
  INDEX idx_pay_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- announcements
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS announcements (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(200) NOT NULL,
  category     ENUM('general','update','event','alert') NOT NULL DEFAULT 'general',
  target       ENUM('all','members','franchises') NOT NULL DEFAULT 'all',
  content      TEXT         NOT NULL,
  publish_date DATE         NOT NULL,
  created_by   VARCHAR(80)  NOT NULL DEFAULT 'Admin',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ann_date   (publish_date),
  INDEX idx_ann_target (target)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- leads
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS leads (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  franchise_id INT UNSIGNED NOT NULL,
  lead_name    VARCHAR(120) NOT NULL,
  lead_phone   VARCHAR(15)  NOT NULL,
  lead_city    VARCHAR(80)  DEFAULT NULL,
  notes        TEXT         DEFAULT NULL,
  status       ENUM('new','contacted','converted','lost') NOT NULL DEFAULT 'new',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (franchise_id) REFERENCES franchises(id) ON DELETE CASCADE,
  INDEX idx_lead_fr     (franchise_id),
  INDEX idx_lead_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- franchise_income
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS franchise_income (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  franchise_id INT UNSIGNED NOT NULL,
  income_type  ENUM('Direct Bonus','Level Income','Rank Bonus','Matching Bonus') NOT NULL,
  source_name  VARCHAR(120) DEFAULT NULL,
  amount       DECIMAL(10,2) NOT NULL,
  status       ENUM('pending','credited') NOT NULL DEFAULT 'pending',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (franchise_id) REFERENCES franchises(id) ON DELETE CASCADE,
  INDEX idx_fi_franchise (franchise_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- bank_details
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS bank_details (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  member_id    INT UNSIGNED NOT NULL UNIQUE,
  account_no   VARCHAR(20)  DEFAULT NULL,
  ifsc         VARCHAR(15)  DEFAULT NULL,
  account_name VARCHAR(120) DEFAULT NULL,
  upi_id       VARCHAR(80)  DEFAULT NULL,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- contact_messages
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(120) NOT NULL,
  phone      VARCHAR(15)  DEFAULT NULL,
  email      VARCHAR(120) NOT NULL,
  subject    VARCHAR(200) DEFAULT NULL,
  message    TEXT         NOT NULL,
  ip_address VARCHAR(45)  DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_cm_email (email),
  INDEX idx_cm_ip    (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

