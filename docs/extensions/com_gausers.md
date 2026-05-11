# User Membership System (com_gausers)

**Package:** `pkg_gausers` (includes `mod_gausers`, `mod_gausersexecs`, `plg_user_gausers`, `plg_task_gasubscriptions`)  
**Current version:** 6.0.0 (package 6.0)

## Description

A component to manage Joomla user records with membership invoicing. Designed for clubs and community groups that need to maintain membership records for historical and insurance purposes, generate membership invoices, and manage subscription renewals.

## Installation

Install `pkg_gausers` via **Joomla Admin → Extensions → Install → Upload Package File**.

The package includes:
- `com_gausers` — the main membership component
- `mod_gausers` — front-end member listing module
- `mod_gausersexecs` — committee/executives listing module
- `plg_user_gausers` — user plugin integration
- `plg_task_gasubscriptions` — scheduled task for subscription renewals

## Detailed manual

A comprehensive guide covering the full workflow — member listing, invoicing, application forms, and all configuration options — is available in the [User Membership System Manual](../manuals/com_gausers-manual.md).

## Key features

- Member listing with contact details (name, address, phone as clickable links)
- Membership officer view with action buttons per member
- Membership invoicing with PDF generation
- Pro-rata invoice calculation
- PayPal payment support
- Automatic disable/reinstate of users by invoice status
- Membership application form with PDF generation (no DB record created)
- Members directory PDF and CSV extract
- Audit trail for all record changes
- Finance component integration for payment recording
- Working With Children (WWC) field management
- Guided tours for initial setup

## Integration

- **[com_gafinance](com_gafinance.md):** When a membership invoice is marked paid, a finance transaction can be created automatically (configure under Finance tab in component options).
- **[plg_user_profileb4wdc](plg_user_profileb4wdc.md):** Provides the 4WD-specific profile fields referenced by the `Profile Suffix` setting in Base Parameters.
- **[plg_task_gasubscriptions](plg_task_gasubscriptions.md):** Scheduler task for automated subscription renewal notifications.

## Version history

| Version | Notes |
|---------|-------|
| 6.0.0 (pkg 6.0) | Adds `plg_task_gasubscriptions` for subscription automation |
| 5.4.x (pkg 5.x) | Joomla 5 compatibility updates |
| 5.1.6 | Previous production version |
