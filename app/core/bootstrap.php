<?php
/**
 * Database bootstrap: ensures required tables/columns/indexes exist
 * and seeds default reference data if missing.
 */

function ln_db(): PDO { return db_connect(); }

function ln_table_exists(PDO $dbh, string $table): bool {
  $st = $dbh->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
  $st->execute([$table]);
  return (bool)$st->fetchColumn();
}

function ln_column_exists(PDO $dbh, string $table, string $column): bool {
  $st = $dbh->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1");
  $st->execute([$table,$column]);
  return (bool)$st->fetchColumn();
}

function ln_index_exists(PDO $dbh, string $table, string $index): bool {
  $st = $dbh->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = ?");
  $st->execute([$index]);
  return (bool)$st->fetch();
}

function ln_exec_silent(PDO $dbh, string $sql, array $params = []): void {
  try { $st = $dbh->prepare($sql); $st->execute($params); } catch (Throwable $e) { /* ignore */ }
}

function lifenav_bootstrap(): void {
  $dbh = ln_db();
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // tenants
  if (!ln_table_exists($dbh,'tenants')) {
    ln_exec_silent($dbh, "CREATE TABLE tenants (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  // users
  if (!ln_table_exists($dbh,'users')) {
    ln_exec_silent($dbh, "CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      tenant_id INT NOT NULL,
      name VARCHAR(255) NOT NULL,
      email VARCHAR(255) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      role VARCHAR(32) NOT NULL DEFAULT 'user',
      created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_users_tenant (tenant_id),
      FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  // categories
  if (!ln_table_exists($dbh,'categories')) {
    ln_exec_silent($dbh, "CREATE TABLE categories (
      id INT AUTO_INCREMENT PRIMARY KEY,
      tenant_id INT NULL,
      name VARCHAR(100) NOT NULL,
      active TINYINT(1) NOT NULL DEFAULT 1,
      UNIQUE KEY uq_categories_tenant_name (tenant_id, name),
      INDEX idx_categories_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  } else {
    if (!ln_column_exists($dbh,'categories','tenant_id')) ln_exec_silent($dbh, "ALTER TABLE categories ADD COLUMN tenant_id INT NULL");
    if (!ln_column_exists($dbh,'categories','active')) ln_exec_silent($dbh, "ALTER TABLE categories ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1");
    if (!ln_index_exists($dbh,'categories','idx_categories_tenant')) ln_exec_silent($dbh, "CREATE INDEX idx_categories_tenant ON categories(tenant_id)");
    if (!ln_index_exists($dbh,'categories','uq_categories_tenant_name')) ln_exec_silent($dbh, "CREATE UNIQUE INDEX uq_categories_tenant_name ON categories(tenant_id, name)");
  }

  // subcategories
  if (!ln_table_exists($dbh,'subcategories')) {
    ln_exec_silent($dbh, "CREATE TABLE subcategories (
      id INT AUTO_INCREMENT PRIMARY KEY,
      tenant_id INT NULL,
      category_id INT NOT NULL,
      name VARCHAR(100) NOT NULL,
      active TINYINT(1) NOT NULL DEFAULT 1,
      UNIQUE KEY uq_subcategories_tenant_cat_name (tenant_id, category_id, name),
      INDEX idx_subcategories_tenant_cat (tenant_id, category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  } else {
    if (!ln_column_exists($dbh,'subcategories','tenant_id')) ln_exec_silent($dbh, "ALTER TABLE subcategories ADD COLUMN tenant_id INT NULL");
    if (!ln_column_exists($dbh,'subcategories','active')) ln_exec_silent($dbh, "ALTER TABLE subcategories ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1");
    if (!ln_index_exists($dbh,'subcategories','idx_subcategories_tenant_cat')) ln_exec_silent($dbh, "CREATE INDEX idx_subcategories_tenant_cat ON subcategories(tenant_id, category_id)");
    if (!ln_index_exists($dbh,'subcategories','uq_subcategories_tenant_cat_name')) ln_exec_silent($dbh, "CREATE UNIQUE INDEX uq_subcategories_tenant_cat_name ON subcategories(tenant_id, category_id, name)");
  }

  // payment_methods
  if (!ln_table_exists($dbh,'payment_methods')) {
    ln_exec_silent($dbh, "CREATE TABLE payment_methods (
      id INT AUTO_INCREMENT PRIMARY KEY,
      tenant_id INT NULL,
      name VARCHAR(64) NOT NULL,
      active TINYINT(1) NOT NULL DEFAULT 1,
      UNIQUE KEY uq_payment_methods_tenant_name (tenant_id, name),
      INDEX idx_payment_methods_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  } else {
    if (!ln_column_exists($dbh,'payment_methods','tenant_id')) ln_exec_silent($dbh, "ALTER TABLE payment_methods ADD COLUMN tenant_id INT NULL");
    if (!ln_column_exists($dbh,'payment_methods','active')) ln_exec_silent($dbh, "ALTER TABLE payment_methods ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1");
    if (!ln_index_exists($dbh,'payment_methods','idx_payment_methods_tenant')) ln_exec_silent($dbh, "CREATE INDEX idx_payment_methods_tenant ON payment_methods(tenant_id)");
    if (!ln_index_exists($dbh,'payment_methods','uq_payment_methods_tenant_name')) ln_exec_silent($dbh, "CREATE UNIQUE INDEX uq_payment_methods_tenant_name ON payment_methods(tenant_id, name)");
  }

  // expenses
  if (!ln_table_exists($dbh,'expenses')) {
    ln_exec_silent($dbh, "CREATE TABLE expenses (
      id INT AUTO_INCREMENT PRIMARY KEY,
      tenant_id INT NOT NULL,
      user_id INT NOT NULL,
      date DATE NOT NULL,
      amount_cents INT NOT NULL,
      currency VARCHAR(8) NOT NULL DEFAULT 'CAD',
      category_id INT NOT NULL,
      subcategory_id INT NULL,
      payment_method_id INT NULL,
      merchant VARCHAR(64) NULL,
      note VARCHAR(255) NULL,
      created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_expenses_tenant_date (tenant_id, date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  // tags
  if (!ln_table_exists($dbh,'tags')) {
    ln_exec_silent($dbh, "CREATE TABLE tags (
      id INT AUTO_INCREMENT PRIMARY KEY,
      tenant_id INT NOT NULL,
      name VARCHAR(64) NOT NULL,
      UNIQUE KEY uq_tags_tenant_name (tenant_id, name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  // expense_tags
  if (!ln_table_exists($dbh,'expense_tags')) {
    ln_exec_silent($dbh, "CREATE TABLE expense_tags (
      expense_id INT NOT NULL,
      tag_id INT NOT NULL,
      PRIMARY KEY (expense_id, tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  // monthly_expense_totals
  if (!ln_table_exists($dbh,'monthly_expense_totals')) {
    ln_exec_silent($dbh, "CREATE TABLE monthly_expense_totals (
      tenant_id INT NOT NULL,
      year_month CHAR(7) NOT NULL,
      category_id INT NOT NULL,
      currency VARCHAR(8) NOT NULL DEFAULT 'CAD',
      total_cents BIGINT NOT NULL DEFAULT 0,
      PRIMARY KEY (tenant_id, year_month, category_id, currency)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  }

  // Seed global defaults if tables are empty and there's no tenant context yet
  try {
    $hasCats = (int)$dbh->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($hasCats === 0) {
      $ins = $dbh->prepare("INSERT INTO categories (tenant_id,name,active) VALUES (NULL,?,1)");
      foreach (['Transportation','Accommodation','Travel & Entertainment','Health'] as $n) { $ins->execute([$n]); }
    }
    $hasSubs = (int)$dbh->query("SELECT COUNT(*) FROM subcategories")->fetchColumn();
    if ($hasSubs === 0) {
      // map names to ids
      $rows = $dbh->query("SELECT id,name FROM categories WHERE tenant_id IS NULL")->fetchAll();
      $catId = [];
      foreach ($rows as $r) $catId[$r['name']] = (int)$r['id'];
      $ins = $dbh->prepare("INSERT INTO subcategories (tenant_id,category_id,name,active) VALUES (NULL,?,?,1)");
      foreach ([
        'Transportation' => ['Car Insurance','Fuel','Parking','Public Transit','Other'],
        'Accommodation'  => ['Rent','Mortgage','Utilities','Internet','Other'],
        'Travel & Entertainment' => ['Flights','Hotels','Dining','Tours','Visas','Movies','Games','Sports','Concerts','Other'],
        'Health' => ['Doctor Visits','Medications','Dental','Vision','Fitness','Other'],
      ] as $catName => $subs) {
        $cid = $catId[$catName] ?? null; if (!$cid) continue;
        foreach ($subs as $s) { $ins->execute([$cid,$s]); }
      }
    }
    $hasPM = (int)$dbh->query("SELECT COUNT(*) FROM payment_methods")->fetchColumn();
    if ($hasPM === 0) {
      $ins = $dbh->prepare("INSERT INTO payment_methods (tenant_id,name,active) VALUES (NULL,?,1)");
      foreach (['Cash','Debit','Credit','E-Transfer','Other'] as $n) { $ins->execute([$n]); }
    }
  } catch (Throwable $e) { /* ignore seeding errors */ }
}
