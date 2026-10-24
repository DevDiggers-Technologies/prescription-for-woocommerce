# DevDiggers Prescription for WooCommerce

Require a prescription upload at WooCommerce checkout, hold the order until a pharmacist approves it, and keep every file private on your server.

- **Author:** [DevDiggers](https://devdiggers.com/)
- **Version:** 1.0.0
- **License:** GPLv3 or later
- **Requires:** WordPress 6.5+, WooCommerce 9.0+, PHP 7.4+
- **Compatible with:** HPOS, WooCommerce Cart and Checkout blocks

The WordPress.org listing text lives in `readme.txt`.

## What it does

Built for online pharmacies, chemists and medical stores. You choose which products need a prescription, customers upload it on the cart or checkout page, and the order waits in a holding status until a reviewer approves or rejects the prescription from the WooCommerce order screen. Files never go to an outside service.

## Setup

1. Activate the plugin and run the setup wizard.
2. Turn it on under **Prescriptions > Configuration > General** and pick the prescription categories.
3. Set the holding, approval and rejection statuses under **Configuration > Review Workflow**.

## Free vs Pro

| Feature | Free | Pro |
| --- | --- | --- |
| Category, product and exclusion rules | Yes | Yes |
| Upload on cart, checkout (classic and blocks) and order page | Yes | Yes |
| Server side and Store API enforcement | Yes | Yes |
| Attach Later for logged in customers | Yes | Yes |
| Holding status that survives payment gateways | Yes | Yes |
| Approve, reject, request information, automatic order status | Yes | Yes |
| Private vault with checked file links | Yes | Yes |
| Customer and store emails, self service re-upload | Yes | Yes |
| Dashboard, Orders screen with tabs and search, setup wizard | Yes | Yes |
| Review workspace, bulk decisions, saved replies | No | Yes |
| Pharmacist role, assignment, review targets | No | Yes |
| Prescription details, prescriber registry, verification links | No | Yes |
| Duplicate file warning, audit trail, access log | No | Yes |
| My Account tab, reuse and refills, validity and expiry | No | Yes |
| Reminder emails, email editor, quantity limits | No | Yes |
| Consent records, data retention | No | Yes |
| Pharmacy analytics and CSV export | No | Yes |

[DevDiggers Prescription for WooCommerce Pro](https://devdiggers.com/product/woocommerce-medical-prescription-attachment/) installs on top of this plugin and uses the same order data.

## Development

```bash
npm install
npm run build
php bin/test-free-boundary.php
```

`bin/test-free-boundary.php` fails if any Pro file, handler, setting, scheduled task, licence call or Pro identifier finds its way back into Free.

## Support

- [WordPress.org support forum](https://wordpress.org/support/plugin/devdiggers-prescription-for-woocommerce/)
- [Documentation](https://docs.devdiggers.com/woocommerce-medical-prescription-attachment/)
- [Contact DevDiggers](https://devdiggers.com/contact/)

## Changelog

### 1.0.0
- Initial free release.
