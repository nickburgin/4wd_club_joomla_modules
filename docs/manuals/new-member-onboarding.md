# New Member Onboarding Guide

This guide walks a Membership Officer through the full process of onboarding a new club member — from receiving an application through to the member having active site access and a paid invoice on record.

---

## Overview

A new member goes through these stages:

1. [Application received](#1-application-received)
2. [Member record created in com_gausers](#2-create-the-member-record)
3. [Membership invoice issued and paid](#3-membership-invoice)
4. [Member enabled and welcome email sent](#4-enable-and-welcome)
5. [Finance record confirmed (if com_gafinance is installed)](#5-finance-record)

---

## Prerequisites

Before onboarding a new member, confirm the following are configured:

- At least one **Membership Type** exists (Admin → Components → User Memberships → Membership Types)
- A **Welcome Article** is set in com_gausers configuration (Membership section → Welcome Article)
- Bank or PayPal details are filled in (Finance section of configuration) so the invoice PDF is correct
- The **Left Club Group** and **Deceased Group** are configured (so the new member is not accidentally placed in one of these)

---

## 1. Application Received

### Online application form

If the **Member Application** option is enabled in com_gausers configuration, applicants can fill in an online form. On submission, a PDF application form is generated and emailed to the applicant. They sign and return it to the Membership Officer — no database record is created at this point.

### Paper or email application

If your club does not use the online form, collect the following details before proceeding:

- Full name
- Email address
- Phone number
- Postal address
- Preferred membership type
- Joining date

---

## 2. Create the Member Record

1. Go to **Joomla Admin → Components → User Memberships** (or the front-end Members listing if your menu is configured).
2. Click **+ New Member** at the bottom of the listing.
3. Fill in all available fields across the **Membership** and **Contact** tabs:
   - Member Name, Email, Phone
   - Address fields
   - Membership Type
   - Any additional profile fields (e.g. [plg_user_profileb4wdc](../extensions/plg_user_profileb4wdc.md) fields if installed)
4. Click **Save**.

On save, com_gausers automatically:

- Checks the email address is unique
- Creates a Joomla user account with an auto-generated password
- Sends Joomla's standard new-user email (username and password) to the member
- Generates a membership invoice PDF and emails it to the member
- Sends the configured Welcome Article email (if one is set)

> **Note:** If the member has no email address, use your club's placeholder prefix (configured under Base Parameters → Exclude Email Prefix, e.g. `noemail@`). Emails to placeholder addresses are silently skipped.

---

## 3. Membership Invoice

### Checking the invoice

The invoice is generated automatically on member creation. To view or resend it:

1. Go to **Components → User Memberships → Membership Invoices**.
2. Locate the new member's invoice.
3. Click the **Envelope** icon to resend the PDF, or click the invoice number to open the PDF directly.

### Recording payment

When the member pays:

1. Click the **Credit card** icon on their invoice row.
2. Enter the date paid (leave blank to use today's date) and confirm the amount.
3. Click **Save**.

The invoice disappears from the unpaid list and the member is re-enabled if they were disabled.

> If **Incorporate Finance** is enabled in configuration and com_gafinance is installed, a finance transaction is created automatically when an invoice is marked paid. See [step 5](#5-finance-record).

---

## 4. Enable and Welcome

### Member enabled status

A newly created member is enabled (financial) by default. If your configuration sets **Disable Users** to automatically disable members on invoice generation, you may need to re-enable after payment. In the Members listing:

- A **green tick** in the Actions column means the member is enabled.
- A **red cross** means they are disabled. Click it to toggle.

### Sending the welcome email manually

If the welcome email was not sent on creation (e.g. a placeholder address was used and the address has since been corrected):

1. Find the member in the listing.
2. Click the **Envelope** icon in their Actions column.
3. Confirm the send.

---

## 5. Finance Record

If com_gafinance is installed and **Incorporate Finance** is enabled:

1. Go to **Components → Finance**.
2. Confirm a transaction entry exists for the membership payment with the correct category (as configured under Finance → Finance Category ID).
3. If it is missing (e.g. the member paid before the integration was configured), create the transaction manually.

---

## Common Issues

| Problem | Resolution |
|---------|-----------|
| Duplicate email error on save | The email address is already registered to another Joomla user. Check under Users → Manage and either remove the old account or use a placeholder address. |
| Member did not receive welcome email | Check the member's email address is correct and is not a placeholder prefix. Resend via the Envelope icon in the Members listing. |
| Invoice PDF not generated | Verify the **Invoice Location** folder exists and is writable by the web server. Check com_gausers configuration → Invoicing → Invoice Location. |
| Finance transaction not created | Ensure **Incorporate Finance** is enabled and **Finance Category ID** points to a valid com_gafinance category. |
| Member cannot log in | Confirm the Joomla user account is enabled under Users → Manage. The user may also need to be added to the correct user group if group assignment is not automatic. |

---

## Couples and family memberships

The system has built-in support for family/couple memberships where multiple people share one membership number but each have their own login.

### How it works

Family members are linked via a shared profile field (default: `mship_no`). The primary member's number is a plain integer (e.g. `105`). Secondary members use dot-notation sub-numbers (e.g. `105.1`, `105.2`).

During invoice generation, when the system encounters a member whose membership type is in the **Family Memberships** list:
- It looks up all other members whose `mship_no` starts with the same base number
- It generates one invoice for the primary member, with the secondary names listed on the PDF
- Secondary members (those with a `.` in their `mship_no`) are automatically skipped

### Configuration

Under **Configuration → Membership**:

| Setting | Description |
|---------|-------------|
| Family Memberships | Enable family/couple membership support |
| Primary Indicator | Profile field that identifies the primary (head) member |
| Link Family | Profile field used to link family members (default: `mship_no`) |
| Family Membership Types | Which membership types use the family invoicing logic |

### Setup for a new couple

1. Create both Joomla user accounts
2. Set the primary member's `mship_no` to a plain integer (e.g. `105`)
3. Set the secondary member's `mship_no` to `105.1` (or `105.2` for a third member, etc.)
4. Assign both accounts a membership type that is in the **Family Membership Types** list
5. Ensure **Family Memberships** is enabled in configuration

The invoice will be sent to the primary member only and will list the secondary member's name in parentheses on the PDF.

---

## Related documentation

- [User Membership Package](../extensions/com_gausers.md) — extension overview and installation
- [User Membership System Manual](com_gausers-manual.md) — full administrator reference
- [4WD User Profile Plugin](../extensions/plg_user_profileb4wdc.md) — extra profile fields added to member records
- [Finance Package](../extensions/com_gafinance.md) — finance integration
