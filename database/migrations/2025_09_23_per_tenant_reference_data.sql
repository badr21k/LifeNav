-- Migration: Per-tenant reference data for categories, subcategories, payment_methods
-- Created: 2025-09-23
-- Purpose: Add tenant_id and proper indexes to reference tables so each account (tenant)
--          can have isolated categories, subcategories, and payment methods.

START TRANSACTION;

-- 1) Categories: add tenant_id + active, and unique index per tenant
ALTER TABLE categories
  ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id,
  ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1;

CREATE INDEX IF NOT EXISTS idx_categories_tenant ON categories(tenant_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_categories_tenant_name ON categories(tenant_id, name);

-- 2) Subcategories: add tenant_id + active, and unique index per tenant/category/name
ALTER TABLE subcategories
  ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id,
  ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1;

CREATE INDEX IF NOT EXISTS idx_subcategories_tenant_cat ON subcategories(tenant_id, category_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_subcategories_tenant_cat_name ON subcategories(tenant_id, category_id, name);

-- 3) Payment methods: add tenant_id + active, and unique index per tenant
ALTER TABLE payment_methods
  ADD COLUMN IF NOT EXISTS tenant_id INT NULL AFTER id,
  ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1;

CREATE INDEX IF NOT EXISTS idx_payment_methods_tenant ON payment_methods(tenant_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_payment_methods_tenant_name ON payment_methods(tenant_id, name);

COMMIT;

-- Optional STEP A: Convert existing global rows to a specific tenant (e.g., tenant_id = 1)
-- Uncomment and run if you want current rows to belong to tenant 1.
-- UPDATE categories SET tenant_id = 1 WHERE tenant_id IS NULL;
-- UPDATE subcategories SET tenant_id = 1 WHERE tenant_id IS NULL;
-- UPDATE payment_methods SET tenant_id = 1 WHERE tenant_id IS NULL;

-- Optional STEP B: If you want to keep existing rows global (tenant_id IS NULL),
-- simply leave them as is. New tenants will get their own default rows on registration
-- via app/controllers/register.php::seedReferenceData().
