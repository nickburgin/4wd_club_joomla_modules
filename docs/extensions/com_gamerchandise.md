# Merchandise Management (com_gamerchandise)

**Current version:** 4.1.2

## Description

A component for managing a club merchandise catalogue and member orders, including stock tracking, order status management, PDF order documents, and optional finance integration.

## Installation

Install `com_gamerchandise` via **Joomla Admin → Extensions → Install → Upload Package File**.

## Key features

- Product catalogue with categories, pricing, and images
- Member order (cart) creation and management
- Order status workflow: Created → Unpaid → Paid → Ordered → Delivered
- PDF order document generation with custom header
- Member discount support
- Optional public (non-member) sales
- Stock on hand tracking
- Email notifications to merchandise officer on new orders
- Finance integration for income/expense recording
- Joomla workflow, custom fields, and content history support

## Configuration options

### Products & orders

| Parameter | Description |
|-----------|-------------|
| Products Header Text | Introductory text shown on the products page |
| Purchase Order Emails | Send email notifications on new orders |
| PO Email Text | Custom template for purchase order emails |
| Notify Merchandise Officer | Notify designated officer on each new order |
| Merchandise Email | Contact email address for orders |
| PO Contact Name / Phone | Contact person details printed on order documents |
| Report Header Image | Logo image printed on order PDFs |
| Image Format | Header image format (JPG, PNG, GIF) |
| Order Instructions | Instruction text printed on order documents |

### Sales settings

| Parameter | Description |
|-----------|-------------|
| Stock on Hand | Enable stock tracking and display |
| Public Sales | Allow non-members to purchase |
| Welcome Note | Joomla article shown to public buyers |
| Ignore Categories | Product categories hidden from public |

### Discounts & site details

| Parameter | Description |
|-----------|-------------|
| Member Discount | Enable a percentage discount for members |
| Discount Amount | Discount percentage to apply |
| ABN / Address / Phone | Club details printed on order documents |
| Profile Prefix | User profile prefix for member detection (default `b4wdc`) |

### Finance integration

| Parameter | Description |
|-----------|-------------|
| Incorporate Finance | Create finance transactions when orders are paid |
| Finance Category | com_gafinance category for merchandise income |

## Integration

- **[com_gafinance](com_gafinance.md):** Optionally creates income transactions when orders are marked paid.
- **[plg_user_profileb4wdc](plg_user_profileb4wdc.md):** Member detection uses the configured profile prefix to identify members and apply discounts.

## Version history

| Version | Notes |
|---------|-------|
| 4.1.2 | Current release |
