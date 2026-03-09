# Miaolin B2B Integration: Daily Data Import Specifications

## Overview

This document outlines the required data files for daily automated import from Miaolin's BI system to the Craveva platform.

**Data Format Requirements:**

- **Format:** CSV (Comma Separated Values)
- **Encoding:** UTF-8 (Critical for Traditional Chinese characters)
- **Frequency:** Daily (e.g., 06:00 AM) or multiple times per day.
- **Header Row:** Required.

---

## 1. Customer Master (`customers.csv`)

Used for authentication (Username + Phone) and credit validation.

| Field Name        | Type    | Required | Description                                            |
| :---------------- | :------ | :------- | :----------------------------------------------------- |
| `customer_code`   | String  | Yes      | Unique ID in Digiwin ERP (e.g., `CUST001`).            |
| `customer_name`   | String  | Yes      | Full business name (e.g., `鼎泰豐`).                   |
| `auth_username`   | String  | Yes      | Username provided to customer for login.               |
| `auth_phone`      | String  | Yes      | Verified phone number (e.g., `0912345678`).            |
| `credit_limit`    | Decimal | Yes      | Max credit amount (e.g., `50000.00`).                  |
| `current_balance` | Decimal | Yes      | Current outstanding balance (for credit check).        |
| `status`          | String  | Yes      | `Active` or `Inactive`.                                |
| `price_tier`      | Integer | No       | Default price level (1-5) if no contract price exists. |

---

## 2. Product Master (`products.csv`)

Base product information and standard pricing tiers.

| Field Name      | Type    | Required | Description                                 |
| :-------------- | :------ | :------- | :------------------------------------------ |
| `product_code`  | String  | Yes      | Unique Item ID (e.g., `FLOUR-A`).           |
| `product_name`  | String  | Yes      | Item Name (e.g., `特級麵粉 25kg`).          |
| `uom`           | String  | Yes      | Unit of Measure (e.g., `Bag`, `Box`, `kg`). |
| `category`      | String  | No       | Product Category (e.g., `Flour`, `Oil`).    |
| `price_level_1` | Decimal | Yes      | Standard List Price.                        |
| `price_level_2` | Decimal | No       | Tier 2 Price.                               |
| `price_level_3` | Decimal | No       | Tier 3 Price.                               |
| `price_level_4` | Decimal | No       | Tier 4 Price.                               |
| `price_level_5` | Decimal | No       | Lowest Tier Price.                          |
| `is_active`     | Boolean | Yes      | `1` for active, `0` for discontinued.       |

---

## 3. Customer Contract Prices (`contract_prices.csv`)

Specific negotiated prices for individual customers. This overrides standard tier pricing.

| Field Name       | Type    | Required | Description                                |
| :--------------- | :------ | :------- | :----------------------------------------- |
| `customer_code`  | String  | Yes      | Must match `customers.csv`.                |
| `product_code`   | String  | Yes      | Must match `products.csv`.                 |
| `contract_price` | Decimal | Yes      | Special negotiated price (e.g., `450.00`). |
| `start_date`     | Date    | No       | Validity start (YYYY-MM-DD).               |
| `end_date`       | Date    | No       | Validity end (YYYY-MM-DD).                 |

---

## 4. Inventory Snapshot (`inventory.csv`)

Real-time stock availability for order validation.

| Field Name      | Type    | Required | Description                                  |
| :-------------- | :------ | :------- | :------------------------------------------- |
| `product_code`  | String  | Yes      | Must match `products.csv`.                   |
| `warehouse_id`  | String  | No       | Default warehouse if not specified.          |
| `qty_available` | Decimal | Yes      | Total physical stock available for sale.     |
| `qty_safety`    | Decimal | No       | Safety stock buffer (optional, default 20%). |

---

## 5. Order Status (`orders.csv`)

Allows customers to check the status of their orders via the AI agent.

| Field Name           | Type   | Required | Description                                              |
| :------------------- | :----- | :------- | :------------------------------------------------------- |
| `order_id`           | String | Yes      | Unique Order ID in ERP (e.g., `ORD-2023-001`).           |
| `customer_code`      | String | Yes      | Must match `customers.csv`.                              |
| `order_date`         | Date   | Yes      | Date of order (YYYY-MM-DD).                              |
| `status`             | String | Yes      | e.g., `Processing`, `Shipped`, `Delivered`, `Cancelled`. |
| `tracking_number`    | String | No       | Logistics tracking number (if applicable).               |
| `estimated_delivery` | Date   | No       | Expected delivery date.                                  |

---

## Integration Workflow

1.  **Export:** Miaolin BI system generates these 5 CSV files.
2.  **Upload:** Files are uploaded to the designated secure folder (SFTP / S3 / Google Drive).
3.  **Trigger:**
    - **Option A:** Craveva polls the folder every 15 minutes.
    - **Option B:** BI system calls a Webhook API (`https://api.craveva.com/webhooks/import-sync`) after upload completion.
4.  **Processing:** Craveva validates and updates the database.
5.  **Feedback:** Craveva logs success/error status to a `logs/` folder.
