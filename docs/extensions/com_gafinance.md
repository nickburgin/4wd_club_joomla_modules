# Basic Finance System (com_gafinance)

**Package:** `pkg_gafinance` (includes `mod_gafinance`)  
**Current version:** 5.2.3 (package 5.5)

## Description

A simple finance system for clubs and community groups to manage their income and expenses while providing basic reports for meetings. Reports include Profit & Loss, Cash Summary, and Balance Sheet.

> **This is not a full financial system.** It is designed for simple club bookkeeping, not complex accounting.

## Installation

Install `pkg_gafinance` via **Joomla Admin → Extensions → Install → Upload Package File**. Installing the package ensures both the component and the finance module are installed together.

## Key features

- Transaction management (income and expenses)
- Multi-bank account management and consolidated reporting
- Basic budgeting (monthly, quarterly, or annual)
- PDF invoicing with optional logo and payment instructions
- Audit logging of all transactions and changes
- PayPal and accounts payable tracking
- GST support (typically disabled for non-profit groups)
- Receipt file uploads (PDF/JPG/PNG)
- Email audit file export
- Profit & Loss, Cash Summary, Balance Sheet, Bank Reconciliation, GST Summary, and Depreciation Schedule reports
- Guided tours for initial setup

## Initial setup

Before using the component, you must create at least one **bank account** (Admin → Finance → Accounts) and the relevant **categories** (Admin → Finance → Categories, or Joomla's standard category manager filtered to `com_gafinance`).

Each bank account has:
- Account name
- BSB / Sort code
- Account number

An **opening balance transaction** (type `Z`) should be created for each account before adding regular transactions. All balance calculations are made relative to the most recent `Z` record.

Default categories created on installation include: General Operations, Bank Charges or Interest, Membership, Merchandise, Donations, IT Related, Advertising or Sponsorship, Training, Venue Costs, Miscellaneous.

## Transactions

Transactions are the core record type. Each transaction has:

| Field | Description |
|-------|-------------|
| Type | `I` = Income, `E` = Expense, `D` = Journal entry, `Z` = Opening balance |
| Date | Transaction date |
| Amount | Positive = income/credit, negative = expense/debit |
| Category | Links to a `com_gafinance` Joomla category |
| Account | Which bank account this belongs to |
| Reference | Cheque number, invoice reference, etc. |
| Receipt | Optional attached file (PDF/JPG/PNG/GIF, default max 300 KB) |
| GST amount | Manually entered GST component (only when GST is enabled) |

> GST amounts are **not** calculated automatically — they must be entered manually per transaction.

> Deleting an account or category does **not** cascade-delete its transactions. Manual cleanup is required.

## Reports

| Report | Description |
|--------|-------------|
| Cash Book Summary | Opening balance, all transactions, and closing balance for a selected period and account(s) |
| Profit & Loss | Income and expenses by category for a date range |
| Balance Sheet | Point-in-time financial position — assets, liabilities, equity |
| Bank Reconciliation | Matches transactions against bank statement items |
| Depreciation Schedule | Asset values and depreciation over a period |
| GST Summary | GST collected and claimable GST for a date range |

All reports support date range filtering and account selection. When `Combine Accounts` is enabled in configuration, reports can aggregate across multiple accounts.

## Finance module (mod_gafinance)

Displays a running transaction list for a selected account in a front-end module position. Shows date, category, description, debit/credit columns, and running balance. For membership-category transactions, the member's name is shown instead of the description. Only visible to logged-in users.

## Configuration options

### Business details

| Parameter | Description |
|-----------|-------------|
| ABN | Business registration number — printed on invoices |
| Address / Suburb / Phone / Fax | Club contact details for invoices |

### Transactions

| Parameter | Description |
|-----------|-------------|
| Auto Negative | Automatically set expense transactions to negative amounts |
| Activity Logging | Log all changes to transactions (can grow large) |
| Exchange Rate | Multiplier for a secondary currency (e.g. PayPal USD → AUD) |
| Use PayPal | Track PayPal AUD and USD balances separately |
| GST | Enable GST tracking; set rate (default 10%) |
| Safe Files | Permitted file types for receipt uploads |
| Max File Size | Upload size limit in bytes (default 300,000) |
| Email Audit | Send audit file by email |
| Owner Category | Category used to identify owner drawdown transactions |

### Reporting & accounts

| Parameter | Description |
|-----------|-------------|
| Combine Accounts | Merge multiple accounts into consolidated reports |
| Select Accounts | Which accounts to combine (when enabled) |
| Combine Reports | Show combined and individual account reports together |
| Budget | Enable budgeting feature |
| Budget Regularity | Monthly, quarterly, or annual budget periods |

### Invoicing

| Parameter | Description |
|-----------|-------------|
| Include Invoices | Source for invoice numbering: None, Timesheets, or Finance |
| Invoice Number Offset | Starting offset for invoice numbers (default 900,000) |
| Invoice Prefix | Prefix for invoice numbers (default `BECS`) |
| Dummy Email | Placeholder email for invoices with no recipient |
| Bank / BSB / Account | Fallback bank details printed on invoices |
| Header Image | Logo printed at the top of invoice PDFs |
| Invoice Category | Joomla category used for finance invoices |
| Age to Hide | Hide invoices older than this many days (default 90) |
| Email Text | Template text for invoice emails |
| Payment Instructions | Text printed below bank details on invoices |

### Membership settings

| Parameter | Description |
|-----------|-------------|
| Single Membership | Enable single-record membership mode |
| Profile Suffix | Profile plugin suffix for partner name lookup (when single membership is on) |

## Integration with com_gausers

When a membership invoice is marked as paid in com_gausers, a finance transaction is automatically created if **Incorporate Finance** is enabled in the Users component configuration.

The transaction created is:
- **Type:** Income (`I`)
- **Date:** Invoice paid date
- **Amount:** Invoice amount
- **Category:** Set by `Finance Category ID` in com_gausers config
- **Account:** Set by `Account Name` in com_gausers config (falls back to account ID 1)
- **Reference:** `Invoice {invoice_id}`
- **Comment:** `Auto Loaded from Members Invoicing`

To configure: in com_gausers → Configuration → Finance, set `Incorporate Finance = Yes`, `Finance Category ID` to the membership income category, and `Account Name` to the correct bank account.

## Version history

| Version | Notes |
|---------|-------|
| 5.2.3 (pkg 5.5) | Combined module and component for Joomla 6 preparation; guided tours for setup |
| 3.3.00–3.3.02 | Date formatting improvements; reports helper restructuring |
| 3.2.04–3.2.05 | Asset thumbnail images in listings |
| 3.0.00–3.1.06 | Receipt storage capability |
| 2.0.01 | Invoice functionality and timesheet invoicing integration |
| 1.3.66–1.3.73 | Owner drawdown option; report refinements |
