# DevDiggers Prescription for WooCommerce

Require a prescription upload at WooCommerce checkout, hold the order until a pharmacist approves it, and keep every file private on your server.

- **Author:** [DevDiggers](https://devdiggers.com/)
- **Version:** 1.1.0
- **License:** GPLv3 or later
- **Requires:** WordPress 6.9+, WooCommerce 9.0+, PHP 7.4+
- **Compatible with:** HPOS, WooCommerce Cart and Checkout blocks

The WordPress.org listing text lives in `readme.txt`.

## What it does

Built for online pharmacies, chemists and medical stores. You choose which products need a prescription, customers upload it on the cart or checkout page, and the order waits in a holding status until a reviewer approves or rejects the prescription from the WooCommerce order screen. Files never go to an outside service.

## Setup

1. Activate the plugin and run the setup wizard.
2. Turn it on under **Prescriptions > Configuration > General** and pick the prescription categories.
3. Set the holding, approval and rejection statuses under **Configuration > Review Workflow**.

## AI assistants (MCP)

Ask Claude, ChatGPT, Cursor, VS Code or any MCP client about your prescription queue in plain words: "How many prescriptions are waiting?", "Which orders have waited longest?", "Does this product need a prescription?"

- Tools for the queue overview, prescriptions per order with filters, one prescription in detail, orders waiting on verification, which products need a prescription and the workflow settings
- A Triage pending prescriptions prompt
- Optional: let the assistant approve, reject or ask for more on a prescription, through the same path as the order screen (order note, order status, customer email), logged under WooCommerce > Status > Logs
- Medical data stays in: file contents are never returned, only file names and the checked link a reviewer can open; customers appear by name and email only
- Same access as the Orders screen: reviewers only, customers and visitors are refused
- Managed in DevDiggers Plugins > AI & MCP: one MCP server for every DevDiggers plugin, an on / off switch per plugin and per write tool (write tools start off), a Try panel and an activity log
- Needs WordPress 6.9+ (Abilities API) and the MCP Adapter, which WooCommerce 10.3+ bundles

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

MCP abilities live in `includes/integrations/mcp-integration.php` under the `prescription/` namespace. Add, change or remove one with the `ddwcmpa_mcp_abilities` filter.

`bin/test-free-boundary.php` fails if any Pro file, handler, setting, scheduled task, licence call or Pro identifier finds its way back into Free.

## Support

- [WordPress.org support forum](https://wordpress.org/support/plugin/devdiggers-prescription-for-woocommerce/)
- [Documentation](https://docs.devdiggers.com/woocommerce-medical-prescription-attachment/)
- [Contact DevDiggers](https://devdiggers.com/contact/)

## Changelog

### 1.1.0
- Added: AI assistants (MCP). Ask Claude, ChatGPT, Cursor, VS Code or any MCP client about the prescription queue, prescriptions per order, orders waiting on verification, products that need a prescription and your workflow settings, plus a Triage pending prescriptions prompt.
- Added: An optional AI tool to approve, reject or ask for more information on a prescription, the same way as the order screen. It stays off until you turn on Allow changes.
- Added: DevDiggers Plugins > AI & MCP page. Connect Claude, ChatGPT, Claude Code, Codex, Cursor, VS Code or any MCP client by signing in and clicking Allow in WordPress, with nothing to copy. Turn each plugin and each write tool on or off, try tools in the Try panel and check the activity log.
- Improved: Dashboard cards show the previous period's figure and a short note under every number, and say "None in the previous period" instead of a misleading 100% change.
- Improved: The review queue on the Dashboard shows each customer's avatar.
- Improved: Updated the DevDiggers framework to 1.3.0. On edit screens the title bar with Save stays visible while you scroll on desktop.
- Fixed: Prescription emails to customers and to the store were not being sent. They are sent again.
- Fixed: The Awaiting Review card counted orders still waiting for the customer to upload. It now counts only prescriptions waiting on a reviewer.
- Fixed: Charts and pickers no longer break when a theme or plugin replaces the shared select2 script.

### 1.0.0
- Initial free release.
