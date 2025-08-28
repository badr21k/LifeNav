
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

-- Tabs for Normal and Travel modes
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

-- Persistent user category rows
CREATE TABLE IF NOT EXISTS user_category_rows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    mode ENUM('normal', 'travel') NOT NULL,
    tab_id INT NOT NULL,
    category_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    open_month_ym CHAR(7) NOT NULL,
    current_total_cents BIGINT DEFAULT 0,
    current_entry_count INT DEFAULT 0,
    lifetime_total_cents BIGINT DEFAULT 0,
    lifetime_entry_count INT DEFAULT 0,
    current_currency CHAR(3),
    last_entry_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_category (user_id, mode, tab_id, category_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (tab_id) REFERENCES tabs(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_user_mode (user_id, mode, is_active)
);

-- Entry log (append-only)
CREATE TABLE IF NOT EXISTS entries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    row_id INT NOT NULL,
    user_id INT NOT NULL,
    ts_utc TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    local_date DATE NOT NULL,
    amount_cents INT NOT NULL,
    currency CHAR(3) NOT NULL,
    memo VARCHAR(255),
    source VARCHAR(32) DEFAULT 'ui',
    idempotency_key VARCHAR(64) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (row_id) REFERENCES user_category_rows(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_row_date (row_id, local_date),
    INDEX idx_user_date (user_id, local_date)
);

-- Currency switch history
CREATE TABLE IF NOT EXISTS user_currency_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    effective_from_utc TIMESTAMP NOT NULL,
    currency CHAR(3) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_user_time (user_id, effective_from_utc),
    INDEX idx_user_effective (user_id, effective_from_utc)
);

-- Monthly snapshots for reporting
CREATE TABLE IF NOT EXISTS monthly_snapshots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    row_id INT NOT NULL,
    user_id INT NOT NULL,
    mode ENUM('normal', 'travel') NOT NULL,
    tab_id INT NOT NULL,
    category_id INT NOT NULL,
    month_start DATE NOT NULL,
    month_end DATE NOT NULL,
    total_cents BIGINT NOT NULL,
    predominant_currency CHAR(3),
    entry_count INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_row_month (row_id, month_start),
    FOREIGN KEY (row_id) REFERENCES user_category_rows(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_month (user_id, month_start)
);

-- Per-currency breakdown of monthly snapshots
CREATE TABLE IF NOT EXISTS monthly_snapshot_subtotals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    snapshot_id INT NOT NULL,
    currency CHAR(3) NOT NULL,
    total_cents BIGINT NOT NULL,
    entry_count INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_snapshot_currency (snapshot_id, currency),
    FOREIGN KEY (snapshot_id) REFERENCES monthly_snapshots(id)
);

-- Insert default tabs and categories
INSERT IGNORE INTO tabs (id, mode, name, sort) VALUES
(1, 'normal', 'Transportation', 1),
(2, 'normal', 'Accommodation / Housing', 2),
(3, 'normal', 'Entertainment & Leisure', 3),
(4, 'normal', 'Health & Wellness', 4),
(5, 'normal', 'Essentials', 5),
(6, 'normal', 'Non-Essentials', 6),
(7, 'travel', 'Transportation (Travel)', 1),
(8, 'travel', 'Accommodation (Travel)', 2),
(9, 'travel', 'Food & Dining (Travel)', 3),
(10, 'travel', 'Entertainment & Activities (Travel)', 4),
(11, 'travel', 'Essentials (Travel)', 5);

-- Insert default categories for Normal mode
INSERT IGNORE INTO categories (tab_id, name) VALUES
-- Transportation (Normal)
(1, 'Car purchase/loan'), (1, 'Car insurance'), (1, 'Fuel'), (1, 'Maintenance'), (1, 'Repairs'),
(1, 'Parts & accessories'), (1, 'Registration/licensing'), (1, 'Roadside assistance'), (1, 'Tolls'),
(1, 'Parking'), (1, 'Public transit'), (1, 'Rideshare/taxi'), (1, 'Bike/scooter'), (1, 'Car wash'),
(1, 'Car membership'), (1, 'Driver\'s license renewals'), (1, 'Fines'), (1, 'Other'),

-- Accommodation / Housing (Normal)
(2, 'Rent'), (2, 'Mortgage'), (2, 'Property taxes'), (2, 'Home/tenant insurance'), (2, 'Electricity'),
(2, 'Heating/Hydro'), (2, 'Water/Sewer'), (2, 'Garbage/Recycling fees'), (2, 'Internet'),
(2, 'Cable/Streaming'), (2, 'Mobile/landline'), (2, 'HOA/condo fees'), (2, 'Security system'),
(2, 'Pest control'), (2, 'Cleaning services'), (2, 'Repairs/maintenance'), (2, 'Renovations'),
(2, 'Furniture'), (2, 'Appliances'), (2, 'Tools'), (2, 'Storage unit'), (2, 'Moving costs'),
(2, 'Landscaping/snow removal'), (2, 'Other'),

-- Entertainment & Leisure (Normal)
(3, 'Dining out'), (3, 'Cafes'), (3, 'Snacks/treats'), (3, 'Movies'), (3, 'Concerts'), (3, 'Theater'),
(3, 'Museums'), (3, 'Sports events'), (3, 'Activity fees'), (3, 'Subscriptions'), (3, 'Gaming'),
(3, 'Hobbies/crafts'), (3, 'Books'), (3, 'Streaming rentals'), (3, 'Nightlife'), (3, 'Events/festivals'),
(3, 'Photography'), (3, 'Courses/workshops'), (3, 'Gifts'), (3, 'Other'),

-- Health & Wellness (Normal)
(4, 'Gym membership'), (4, 'Fitness classes'), (4, 'Personal training'), (4, 'Home fitness equipment'),
(4, 'Medicines'), (4, 'Vitamins/supplements'), (4, 'Prescriptions'), (4, 'Pharmacy fees'),
(4, 'GP/Family doctor'), (4, 'Specialists'), (4, 'Hospital/ER'), (4, 'Urgent care'), (4, 'Dental'),
(4, 'Vision'), (4, 'Hearing'), (4, 'Mental health'), (4, 'Physiotherapy'), (4, 'Chiropractor'),
(4, 'Massage therapy'), (4, 'Acupuncture'), (4, 'Alternative medicine'), (4, 'Lab tests'),
(4, 'Medical devices'), (4, 'Health insurance'), (4, 'Travel vaccines'), (4, 'Other'),

-- Essentials (Normal)
(5, 'Groceries'), (5, 'Household supplies'), (5, 'Toiletries'), (5, 'Laundry'), (5, 'Baby supplies'),
(5, 'School supplies'), (5, 'Tuition/fees'), (5, 'Childcare'), (5, 'Transportation passes'),
(5, 'Pet food/care'), (5, 'Basic clothing'), (5, 'Work uniforms'), (5, 'Phone plan'), (5, 'Banking fees'),
(5, 'Taxes'), (5, 'Postage'), (5, 'Community dues'), (5, 'Other'),

-- Non-Essentials (Normal)
(6, 'Club memberships'), (6, 'Premium streaming'), (6, 'Subscription boxes'), (6, 'Premium apps'),
(6, 'Creator memberships'), (6, 'Magazines'), (6, 'Fashion & accessories'), (6, 'Designer items'),
(6, 'Luxury electronics'), (6, 'Collectibles'), (6, 'Special gear'), (6, 'Décor'), (6, 'Non-essential gifts'),
(6, 'Travel splurges'), (6, 'Event splurges'), (6, 'Cosmetics'), (6, 'Impulse buys'), (6, 'Other');

-- Insert default categories for Travel mode
INSERT IGNORE INTO categories (tab_id, name) VALUES
-- Transportation (Travel)
(7, 'Flights'), (7, 'Trains'), (7, 'Buses'), (7, 'Shuttles'), (7, 'Car rental'), (7, 'Fuel'),
(7, 'Taxis/rideshare'), (7, 'Ferries'), (7, 'Cruises'), (7, 'City transport'), (7, 'Baggage fees'),
(7, 'Seat upgrades'), (7, 'Airport parking'), (7, 'Tolls'), (7, 'Travel insurance'), (7, 'Other'),

-- Accommodation (Travel)
(8, 'Hotels'), (8, 'Hostels'), (8, 'Guesthouses'), (8, 'Vacation rentals'), (8, 'Resorts'),
(8, 'Motels'), (8, 'Campsites'), (8, 'Overnight trains'), (8, 'Day rooms'), (8, 'Resort fees'),
(8, 'City/lodging taxes'), (8, 'Other'),

-- Food & Dining (Travel)
(9, 'Restaurants'), (9, 'Cafes'), (9, 'Street food'), (9, 'Groceries'), (9, 'Delivery'),
(9, 'Room service'), (9, 'Snacks'), (9, 'Water/beverages'), (9, 'Specialty dining'), (9, 'Other'),

-- Entertainment & Activities (Travel)
(10, 'Tours'), (10, 'Landmarks/museums'), (10, 'Theme parks'), (10, 'Beach/pool passes'),
(10, 'Outdoor activities'), (10, 'Gear rental'), (10, 'Shows/concerts'), (10, 'Classes/workshops'),
(10, 'Souvenirs'), (10, 'Photography permits'), (10, 'Local SIM/roaming'), (10, 'Other'),

-- Essentials (Travel)
(11, 'Visas/passport'), (11, 'Currency exchange'), (11, 'SIM/roaming plans'), (11, 'Travel insurance'),
(11, 'Travel meds'), (11, 'Safety gear'), (11, 'Emergency fund'), (11, 'Luggage'), (11, 'Power adapters'),
(11, 'Transport cards'), (11, 'Data backups'), (11, 'Other');
