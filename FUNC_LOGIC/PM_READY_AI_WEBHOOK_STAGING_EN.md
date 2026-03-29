# PM Ready - AI Order Webhook (Staging)

## Current Status

- AI inbound webhook is deployed on staging and tested successfully.
- The previous migration failure has been fixed and migrated successfully.
- Endpoint can create both `orders` and `order_items`.
- Basic idempotency is enabled via `external_event_id`.

---

## 1) Webhook Setup Details

**Environment:** Staging  
**Base URL:** `https://staging.craveva.com`

**Webhook URL:**

`POST https://staging.craveva.com/ai-order-webhook/stg-ai-order-20260329-9fA2mK`

**Required headers:**

- `X-AI-Webhook-Secret: stg-ai-order-20260329-9fA2mK`
- `Accept: application/json`

> Note: this is a temporary secret for urgent testing. After PM validation, rotate to a new secret for UAT/production.

---

## 2) Minimum Payload (recommended as form-data or x-www-form-urlencoded)

Required fields:

- `company_id` (example: `1`)
- `client_id` (example: `1`)
- `external_event_id` (must be unique per event)
- `items[0][item_name]`
- `items[0][quantity]`
- `items[0][unit_price]`

Example:

```bash
curl -X POST "https://staging.craveva.com/ai-order-webhook/stg-ai-order-20260329-9fA2mK" \
  -H "Accept: application/json" \
  -H "X-AI-Webhook-Secret: stg-ai-order-20260329-9fA2mK" \
  -d "company_id=1" \
  -d "client_id=1" \
  -d "external_event_id=line-msg-001" \
  -d "note=Order from LINE AI" \
  -d "items[0][item_name]=Coffee test" \
  -d "items[0][quantity]=1" \
  -d "items[0][unit_price]=10000"
```

---

## 3) Sample Responses

### Success (201)

```json
{
    "status": "success",
    "message": "Order created from AI webhook.",
    "data": {
        "order_id": 5,
        "order_number": "ODR#001",
        "company_id": 1,
        "total": 10000
    }
}
```

### Duplicate event (same `external_event_id`)

```json
{
    "status": "success",
    "message": "Event already processed.",
    "duplicate": true
}
```

### Invalid secret (401)

```json
{
    "status": "error",
    "message": "Unauthorized webhook request."
}
```

### Validation error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "company_id": ["The company id field is required."],
        "client_id": ["The client id field is required."],
        "items": ["The items field is required."]
    }
}
```

---

## 4) PM Test Checklist

1. Send a valid webhook payload -> expect `201`.
2. Verify order appears in staging Orders list.
3. Re-send the same `external_event_id` -> expect `duplicate: true`.
4. Send request with wrong secret -> expect `401`.

---

## 5) Scope and Next Steps

### Already completed

- Receive AI webhook.
- Create order + order items.
- Basic duplicate-event handling.

### Recommended next steps

- Rotate webhook secret after PM testing.
- Add dedicated inbound log table (`ai_order_webhook_logs`) for auditability.
- Add automatic mapping (`line_user_id -> client_id`) to remove manual `client_id`.
- Upgrade auth to HMAC signature (instead of shared secret only).
