# Troubleshooting System Hangs

If the system experiences hangs or performance degradation, follow this guide to identify the root cause.

## 1. Immediate Checks
- **Check Logs**:
  - `storage/logs/laravel.log`: Look for "Maximum execution time exceeded" or "Allowed memory size exhausted".
  - Web Server Logs (Nginx/Apache): Check for 504 Gateway Time-out errors.
- **Database**:
  - Check for long-running queries (e.g., `SHOW FULL PROCESSLIST` in MySQL).
  - Check for deadlocks (`SHOW ENGINE INNODB STATUS`).

## 2. Common Causes in Pricing Module
- **Infinite Loops**: Ensure `PricingService::calculate` does not recursively call itself indirectly.
- **Large Data Sets**: If `ClientProductPricing` or `PricingTier` tables grow very large, missing indexes can cause slow queries.
  - *Solution*: Ensure `client_id`, `product_id`, `start_date`, and `end_date` are indexed.
- **External API Calls**: If the pricing logic calls external services (e.g., currency conversion) synchronously, network latency can hang the request.

## 3. Diagnostic Steps
1. **Enable Query Logging**:
   In `.env`, set `DB_LOG_QUERIES=true` (if supported) or use `\DB::enableQueryLog()` in code to debug specific flows.
2. **Profile Performance**:
   Use Laravel Debugbar or Telescope to inspect request timeline.
3. **Monitor Resources**:
   Use `top` or `htop` to check CPU and RAM usage during the hang.

## 4. Specific to Contract Pricing
- The newly added `start_date` and `end_date` checks add complexity to the query.
- Ensure the compound index `(client_id, product_id, is_active)` exists.
- Consider adding an index on `(start_date, end_date)` if the table becomes massive.
