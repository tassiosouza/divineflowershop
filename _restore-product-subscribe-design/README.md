# Restore product page: Subscribe vs One-time purchase design

This folder holds a **backup of the custom product page design** (subscribe / one-time purchase buttons and delivery dropdown). Use it after merging server code so the design is not lost.

## What this restores

- **One-time purchase** and **Subscribe & Save up to 30%** as two side-by-side buttons (with prices)
- **Delivery** dropdown (e.g. "Delivery every 4 weeks") when Subscribe is selected
- Styling: rounded buttons, color #918978 when selected

## How to restore (after merging server code)

Copy the backup over the plugin template:

**From:** `_restore-product-subscribe-design/display-subscription-plans.php`  
**To:** `wp-content/plugins/buy-once-or-subscribe-for-woocommerce-subscriptions/functions/templates/single-product/display-subscription-plans.php`

From project root:
```bash
cp _restore-product-subscribe-design/display-subscription-plans.php wp-content/plugins/buy-once-or-subscribe-for-woocommerce-subscriptions/functions/templates/single-product/display-subscription-plans.php
```

Then clear caches and reload a product page.
