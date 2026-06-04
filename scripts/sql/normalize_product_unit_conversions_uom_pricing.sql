-- Normalize product_unit_conversions pricing (all rows).
-- Run ONLY after column cost_price exists (php artisan migrate).
--
-- Effects:
--   for_sale = 0 (all rows)
--   cost_price = selling_price when selling_price was set, else keep existing cost_price
--   selling_price = NULL (all rows)
--
-- Environments: local, staging, hub — run once per database after backup.

-- Preview (optional):
-- SELECT COUNT(*) AS total FROM product_unit_conversions;
-- SELECT COUNT(*) AS with_selling FROM product_unit_conversions WHERE selling_price IS NOT NULL;
-- SELECT COUNT(*) AS for_sale_on FROM product_unit_conversions WHERE for_sale = 1;

START TRANSACTION;

UPDATE product_unit_conversions
SET cost_price = COALESCE(selling_price, cost_price),
    selling_price = NULL,
    for_sale = 0;

-- Verify before COMMIT:
-- SELECT COUNT(*) FROM product_unit_conversions WHERE selling_price IS NOT NULL OR for_sale = 1;

COMMIT;
