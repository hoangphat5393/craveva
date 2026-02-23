# Contract Pricing API Documentation

## Endpoints

### 1. List Contract Pricings
- **URL**: `/pricing/client-pricing`
- **Method**: `GET`
- **Permissions**: `view_client_pricing`
- **Description**: Returns a list of all contract pricings.

### 2. Create Contract Pricing
- **URL**: `/pricing/client-pricing`
- **Method**: `POST`
- **Permissions**: `add_client_pricing`
- **Parameters**:
  - `client_id` (required, integer): ID of the client.
  - `product_id` (required, integer): ID of the product.
  - `custom_price` (optional, numeric): Custom price override.
  - `discount_type` (optional, string): 'percentage' or 'fixed'.
  - `discount_value` (optional, numeric): Value of the discount.
  - `start_date` (required, date): Start date of the pricing (Format: Company Date Format).
  - `end_date` (required, date): End date of the pricing (Format: Company Date Format, must be after start_date).
- **Validation**:
  - Checks for overlapping dates with existing records for the same client and product.
  - Ensures `end_date` is after or equal to `start_date`.

### 3. Update Contract Pricing
- **URL**: `/pricing/client-pricing/{id}`
- **Method**: `PUT`
- **Permissions**: `edit_client_pricing`
- **Parameters**: Same as Create.
- **Validation**: Same as Create, excluding the current record from overlap check.

### 4. Delete Contract Pricing
- **URL**: `/pricing/client-pricing/{id}`
- **Method**: `DELETE`
- **Permissions**: `edit_client_pricing`

## Validation Logic
- **Overlap Check**: The system prevents creating or updating a contract pricing record if the date range overlaps with any other active record for the same client and product.
- **Date Format**: Dates must strictly follow the company's configured date format.
