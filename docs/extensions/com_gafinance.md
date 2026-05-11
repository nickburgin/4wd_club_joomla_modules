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
- Multi-bank account consolidation
- Basic budgeting
- PDF invoicing with optional logo and payment instructions
- Audit logging of all transactions and changes
- PayPal and accounts payable tracking
- GST support (typically disabled for non-profit groups)
- Receipt file uploads (PDF/JPG)
- Email audit file export
- Profit & Loss, Cash Summary, and Balance Sheet reports
- Guided tours for initial setup

## Configuration options

### Business & membership settings
- Business details included on invoices
- Single or multiple membership record support
- Custom profile plugin configuration
- Partner field support for merged invoice listings

### Transaction management
- Automatic negative values for expense records
- Single extra currency exchange rate support
- Owner drawdown identification
- Receipt file uploads

### Reporting & accounts
- Multi-bank account consolidation and combined reports

### Invoicing
- Invoice number offsetting and prefixes
- Optional header image and payment instructions

### Permissions
- Standard Joomla core permissions
- Special treasurer-level actions

## Integration

Integrates with [com_gausers](com_gausers.md) — when a membership invoice is marked as paid, a finance record can be created automatically. Configure this under **Finance → Incorporate Finance** in the Users component configuration.

## Version history

| Version | Notes |
|---------|-------|
| 5.2.3 (pkg 5.5) | Combined module and component for Joomla 6 preparation; guided tours for setup |
| 3.3.00–3.3.02 | Date formatting improvements; reports helper restructuring |
| 3.2.04–3.2.05 | Asset thumbnail images in listings |
| 3.0.00–3.1.06 | Receipt storage capability |
| 2.0.01 | Invoice functionality and timesheet invoicing integration |
| 1.3.66–1.3.73 | Owner drawdown option; report refinements |
