# Flow: BOM → production order → batch

**Module:** Production — `/account/production/...`

## URLs

- [production.md](../pages/operations/production.md) and related index routes under `production.*`
- BOM and orders: use [00-URL-INDEX.md](../00-URL-INDEX.md) filter `production`

## Steps

1. Define **BOM** (output + components, UOM conversion when mapped).
2. Create **production order**.
3. Issue materials and receive finished goods (warehouse).
4. Track **batches** if enabled.

## More detail

[REFERENCE/BUSINESS-FLOWS-SUMMARY.md](../REFERENCE/BUSINESS-FLOWS-SUMMARY.md)
