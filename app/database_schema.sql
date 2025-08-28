
-- LifeNav Database Schema

-- Users table (enhanced)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    password_hash VARCHAR(255),
    tz VARCHAR(50) DEFAULT 'America/Toronto',
    active_currency CHAR(3) DEFAULT 'CAD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- App tabs for Normal and Travel modes
CREATE TABLE IF NOT EXISTS app_tabs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mode ENUM('normal', 'travel') NOT NULL,
    name VARCHAR(100) NOT NULL,
    sort INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabs alias for compatibility
CREATE TABLE IF NOT EXISTS tabs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mode ENUM('normal', 'travel') NOT NULL,
    name VARCHAR(100) NOT NULL,
    sort INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories within tabs
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tab_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_custom TINYINT(1) DEFAULT 0,
    user_id INT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tab_id) REFERENCES tabs(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_tab_active (tab_id, active),
    INDEX idx_custom_user (is_custom, user_id)
);

-- App categories for compatibility with mode controller
CREATE TABLE IF NOT EXISTS app_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tab_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_custom TINYINT(1) DEFAULT 0,
    user_id INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tab_id) REFERENCES app_tabs(id),
    INDEX idx_tab_active (tab_id, is_active),
    INDEX idx_custom_user (is_custom, user_id)
);

-- User category rows for tracking user selections
CREATE TABLE IF NOT EXISTS user_category_rows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tab_id INT NOT NULL,
    category_id INT NOT NULL,
    mode ENUM('normal', 'travel') NOT NULL,
    current_total_cents BIGINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (tab_id) REFERENCES tabs(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    UNIQUE KEY unique_user_category (user_id, tab_id, category_id, mode),
    INDEX idx_user_mode (user_id, mode)
);

-- Entries for tracking individual transactions
CREATE TABLE IF NOT EXISTS entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    row_id INT NOT NULL,
    user_id INT NOT NULL,
    amount_cents BIGINT NOT NULL,
    currency CHAR(3) NOT NULL,
    memo TEXT,
    idempotency_key VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (row_id) REFERENCES user_category_rows(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_idempotency (user_id, idempotency_key),
    INDEX idx_row_created (row_id, created_at DESC)
);

-- Monthly snapshots for reporting
CREATE TABLE IF NOT EXISTS monthly_snapshots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mode ENUM('normal', 'travel') NOT NULL,
    tab_id INT NOT NULL,
    category_id INT NOT NULL,
    year_month VARCHAR(7) NOT NULL,
    total_cents BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_snapshot (user_id, mode, tab_id, category_id, year_month),
    INDEX idx_user_month (user_id, year_month)
);

-- User currency history
CREATE TABLE IF NOT EXISTS user_currency_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    effective_from_utc TIMESTAMP NOT NULL,
    currency CHAR(3) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_date (user_id, effective_from_utc)
);

-- Insert default tabs for normal mode
INSERT IGNORE INTO app_tabs (id, mode, name, sort, is_active) VALUES
(1, 'normal', 'Housing', 1, 1),
(2, 'normal', 'Transportation', 2, 1),
(3, 'normal', 'Food', 3, 1),
(4, 'normal', 'Health', 4, 1),
(5, 'normal', 'Entertainment', 5, 1);

-- Insert default tabs for travel mode
INSERT IGNORE INTO app_tabs (id, mode, name, sort, is_active) VALUES
(6, 'travel', 'Accommodation', 1, 1),
(7, 'travel', 'Transport', 2, 1),
(8, 'travel', 'Food & Dining', 3, 1),
(9, 'travel', 'Activities', 4, 1),
(10, 'travel', 'Shopping', 5, 1);

-- Insert into tabs table for lifenav compatibility
INSERT IGNORE INTO tabs (id, mode, name, sort) VALUES
(1, 'normal', 'Housing', 1),
(2, 'normal', 'Transportation', 2),
(3, 'normal', 'Food', 3),
(4, 'normal', 'Health', 4),
(5, 'normal', 'Entertainment', 5),
(6, 'travel', 'Accommodation', 1),
(7, 'travel', 'Transport', 2),
(8, 'travel', 'Food & Dining', 3),
(9, 'travel', 'Activities', 4),
(10, 'travel', 'Shopping', 5);

-- Insert default categories
INSERT IGNORE INTO categories (tab_id, name, is_custom, user_id, active) VALUES
-- Housing categories
(1, 'Rent/Mortgage', 0, NULL, 1),
(1, 'Utilities', 0, NULL, 1),
(1, 'Insurance', 0, NULL, 1),
(1, 'Maintenance', 0, NULL, 1),
-- Transportation categories
(2, 'Gas', 0, NULL, 1),
(2, 'Public Transit', 0, NULL, 1),
(2, 'Car Payment', 0, NULL, 1),
(2, 'Maintenance', 0, NULL, 1),
-- Food categories
(3, 'Groceries', 0, NULL, 1),
(3, 'Restaurants', 0, NULL, 1),
(3, 'Takeout', 0, NULL, 1),
-- Health categories
(4, 'Medical', 0, NULL, 1),
(4, 'Dental', 0, NULL, 1),
(4, 'Pharmacy', 0, NULL, 1),
-- Entertainment categories
(5, 'Movies', 0, NULL, 1),
(5, 'Streaming', 0, NULL, 1),
(5, 'Sports', 0, NULL, 1),
-- Travel accommodation categories
(6, 'Hotels', 0, NULL, 1),
(6, 'Airbnb', 0, NULL, 1),
(6, 'Hostels', 0, NULL, 1),
-- Travel transport categories
(7, 'Flights', 0, NULL, 1),
(7, 'Rental Cars', 0, NULL, 1),
(7, 'Local Transport', 0, NULL, 1),
-- Travel food categories
(8, 'Restaurants', 0, NULL, 1),
(8, 'Street Food', 0, NULL, 1),
(8, 'Groceries', 0, NULL, 1),
-- Travel activities categories
(9, 'Tours', 0, NULL, 1),
(9, 'Museums', 0, NULL, 1),
(9, 'Activities', 0, NULL, 1),
-- Travel shopping categories
(10, 'Souvenirs', 0, NULL, 1),
(10, 'Clothing', 0, NULL, 1),
(10, 'Gifts', 0, NULL, 1);

-- Insert into app_categories for mode controller compatibility
INSERT IGNORE INTO app_categories (id, tab_id, name, is_custom, user_id, is_active)
SELECT id, tab_id, name, is_custom, COALESCE(user_id, 0), active
FROM categories;
