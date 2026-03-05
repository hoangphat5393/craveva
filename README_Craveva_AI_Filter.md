# Craveva AI Database Filter Tool

This tool is designed to extract data for a specific company (Craveva AI) from the main database dump (`craveva_test.sql`) and create a standalone database file (`craveva_ai.sql`).

## Features

- **Automated Filtering**: Filters records based on `company_id`.
- **Dependency Handling**: Automatically identifies and includes dependent records using Foreign Key relationships (e.g., users, projects, tasks linked to the company).
- **Data Integrity**: Preserves the original SQL structure and ensures referential integrity.
- **Reusable**: Can be run whenever the source database is updated.

## Prerequisites

- Python 3.x installed on your system.
- The source SQL file (`craveva_test.sql`) must be in the same directory (or configured path).

## Usage

1. **Configure the Script** (Optional):
   Open `filter_craveva.py` and adjust the configuration constants if needed:
   ```python
   INPUT_FILE = 'craveva_test.sql'   # Source file
   OUTPUT_FILE = 'craveva_ai.sql'    # Output file
   TARGET_COMPANY_ID = 20            # Craveva AI Company ID
   ```

2. **Run the Script**:
   Open a terminal in the directory and run:
   ```bash
   python filter_craveva.py
   ```

3. **Check Output**:
   The script will generate `craveva_ai.sql` containing only the data relevant to Craveva AI.

## How It Works

1. **Schema Parsing**: The script reads `CREATE TABLE` statements to understand the database structure, identifying columns and Foreign Key constraints.
2. **Tenant Identification**: It marks tables as "Tenant-specific" if they have a `company_id` column or if they reference other Tenant-specific tables (recursive dependency check). Global tables (like `countries`, `currencies` without company link) are kept entirely.
3. **Data Parsing**: It reads `INSERT INTO` statements, parsing values while handling complex SQL syntax (quoted strings, escaped characters).
4. **Filtering**:
   - **Companies Table**: Keeps only the record with ID 20.
   - **Tenant Tables**: Keeps records where `company_id` is 20.
   - **Dependent Tables**: Keeps records that reference already-kept records in other tables (e.g., a `project_member` is kept if the `project` is kept).
5. **Output Generation**: Writes a new SQL file with the filtered data, ready to be imported into a MySQL/MariaDB database.

## Troubleshooting

- **Missing Data**: If some related data is missing, ensure foreign keys are correctly defined in the source SQL. The script relies on explicit `FOREIGN KEY` constraints.
- **Encoding Errors**: The script uses `utf-8`. Ensure your SQL file is UTF-8 encoded.
