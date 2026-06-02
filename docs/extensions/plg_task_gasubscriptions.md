# Subscriptions Task Plugin (plg_task_gasubscriptions)

**Part of:** `pkg_gausers`  
**Current version:** 5.3

## Description

A Joomla scheduled task plugin that automates membership subscription renewals. When a member's current invoice reaches its expiry date, the plugin blocks their account and generates a renewal invoice.

## Setup

After installing `pkg_gausers`, configure the scheduled task via **Joomla Admin → System → Scheduled Tasks**. The default schedule runs daily at 06:14 AM.

## What it does

Each time the task runs, it:

1. Finds all paid invoices with `end_date = today`
2. For each matching member, applies the following checks in order:
   - **Already invoiced** — if a newer unpaid invoice exists, skip
   - **Excluded member** — if the user ID is in `exclude_member` (com_gausers config), skip
   - **Exempt membership type** — if the membership type is in `mship_exempt` (e.g. Life), skip
   - **Extendable membership type** — if in `mship_extend`, extend the invoice `end_date` by one term instead of generating a new invoice
3. For remaining members: block the user account and generate a renewal invoice via the same path as the manual Generate Invoices button

Emails are sent as part of the invoice generation process, using the same templates as a manual invoice run.

## Dependencies on com_gausers configuration

The plugin reads four parameters directly from the com_gausers component config:

| Config key | Where set | Purpose |
|------------|-----------|---------|
| `exclude_member` | com_gausers → Configuration → Invoicing | User IDs to skip entirely |
| `mship_exempt` | com_gausers → Configuration → Membership | Membership types exempt from renewal (e.g. Life) |
| `mship_extend` | com_gausers → Configuration → Membership | Membership types that auto-extend without a new invoice |
| `profile_suffix` | com_gausers → Configuration → Base Parameters | Profile plugin suffix |

## Version history

| Version | Notes |
|---------|-------|
| 5.3 | Current release; added as part of pkg_gausers 6.0 |
