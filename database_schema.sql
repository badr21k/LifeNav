
-- Core user expense tracking with persistent rows
CREATE TABLE user_expense_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tenant_id INT NOT NULL,
    mode ENUM('normal', travel') NOT NULL,
    tab VARCHAR(50) NOT NULL,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100) NULL,
    current_month_total_cents INT DEFAULT 0,
    lifetime_total_cents INT DEFAULT 0,
    active_currency VARCHAR(3) NOT NULL DEFAULT 'CAD',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_category (user_id, mode, tab, category, subcategory),
    INDEX idx_user_mode (user_id, mode),
    INDEX idx_active (is_active)
);

-- Individual expense entries for detailed history
CREATE TABLE expense_entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_row_id INT NOT NULL,
    amount_cents INT NOT NULL,
    currency VARCHAR(3) NOT NULL,
    memo VARCHAR(255) NULL,
    entry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_row_id) REFERENCES user_expense_categories(id) ON DELETE CASCADE,
    INDEX idx_category_date (category_row_id, entry_date)
);

-- Monthly snapshots for historical tracking
CREATE TABLE monthly_snapshots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_row_id INT NOT NULL,
    user_id INT NOT NULL,
    mode ENUM('normal', 'travel') NOT NULL,
    tab VARCHAR(50) NOT NULL,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100) NULL,
    month_start DATE NOT NULL,
    month_end DATE NOT NULL,
    total_cents INT NOT NULL,
    currency_used VARCHAR(3) NOT NULL,
    entry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_row_id) REFERENCES user_expense_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_monthly (category_row_id, month_start),
    INDEX idx_user_month (user_id, month_start),
    INDEX idx_mode_tab (mode, tab)
);

-- Currency switch tracking
CREATE TABLE currency_switches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    switch_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, switch_date)
);

-- User settings
CREATE TABLE user_settings (
    user_id INT PRIMARY KEY,
    active_currency VARCHAR(3) DEFAULT 'CAD',
    timezone VARCHAR(50) DEFAULT 'UTC',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
