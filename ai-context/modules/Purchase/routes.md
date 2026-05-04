# Routes

- Generated at: 2026-05-04T05:35:06+00:00

- Modules/Purchase/Routes/api.php (lines=?, methods=?)
- Modules/Purchase/Routes/web.php (lines=?, methods=?)

## Route samples

### Modules/Purchase/Routes/api.php


### Modules/Purchase/Routes/web.php

- resource adjustment-reasons
- post apply-quick-action
- resource bills
- get bills/download/{id}
- post bills/send-bill/{billId}
- resource delivery-orders
- post delivery-orders/change-status/{id}
- get delivery-orders/download/{id}
- get delivery-orders/get-items
- get delivery_orders/change_status/{id}
- get fetch-bills/{id?}
- resource grn
- post grn/change-status/{id}
- get grn/download/{id}
- get grn/get-items
- get import
- post import
- post import/process
- resource inventory-files
- get inventory-files/download/{id}
- resource order-report
- resource purchase-contacts
- post purchase-contacts/apply-quick-action
- resource purchase-inventory
- get purchase-inventory/add-files
- get purchase-inventory/adjust-inventory
- post purchase-inventory/apply-quick-action
- post purchase-inventory/change-status
- get purchase-inventory/download/{id}
- get purchase-inventory/layout
- resource purchase-order
- resource purchase-order-file
- get purchase-order-file/download/{id}
- get purchase-order-products
- get purchase-order/add-item
- get purchase-order/change-status/{id}
- get purchase-order/delete-image
- get purchase-order/download/{id}
- post purchase-order/send-order/{orderID}
- get purchase-order/vendor-currency
- get purchase-orders
- resource purchase-products
- get purchase-products/add-images
- get purchase-products/adjust-inventory
- post purchase-products/apply-quick-action
- post purchase-products/change-purchase-allow
- post purchase-products/change-status
- get purchase-products/layout
- get purchase-products/options
- post purchase-products/store-images
- post purchase-products/update-inventory
- resource purchase-settings
- post purchase-settings/update-prefix/{id}
- resource purchase-smtp-settings
- resource reports
- resource sales-do
- get sales-do/get-items
- post sales-do/{id}/cancel
- post sales-do/{id}/confirm
- post sales-do/{id}/deliver
