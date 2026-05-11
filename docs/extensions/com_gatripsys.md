# Trip Management System (com_gatripsys)

**Package:** `pkg_gatripsys` (includes `mod_gatripsys`)  
**Current version:** 5.3.0 (package 5.8)

## Description

A Joomla component that enables registered users to organise trips and allows others to book their attendance. Originally designed for 4WD clubs but adaptable to any car or motorcycle club community.

## Installation

Install `pkg_gatripsys` via **Joomla Admin → Extensions → Install → Upload Package File**.

## Key features

- Trip creation and management by authorised users
- Booking system for trip attendance
- Trip image management
- Trip plan additions and modifications
- Bulk attendee approval
- Cost-per-head charging with invoice generation
- Integration with Finance System for payment tracking
- Manual or automatic invoicing
- Refund processing for cancelled attendances
- Optional trip leader restrictions
- Email notifications to administrators and members
- BCC notifications for new trip announcements
- Guided tours for initial setup

## Configuration options

| Option | Description |
|--------|-------------|
| Charge for Trips | Whether trips have a cost per head |
| Manual / Auto Invoicing | When the invoice is generated — on booking or on approval |
| Trip Leader restrictions | Limit who can create trips |
| ReplyTo options | Email reply-to address for attendee emails |
| Administrator copy | Whether admin receives copy of trip messages |
| BCC Notification | Notify members by BCC when a new trip is posted |

## Integration

Integrates with [com_gafinance](com_gafinance.md) for payment tracking when per-head charges apply.

## Version history

| Version | Notes |
|---------|-------|
| 5.3.0 (pkg 5.8) | Current release |
| 4.4.6 | Review and optimisation for Joomla 5 with namespace updates |
| 4.0.0–4.2.1 | Converted to Joomla 4 compatibility |
| 3.3.00–3.3.07 | Bug fixes, image management, bulk approval, sort order enhancements |
| 3.2.06–3.2.09 | Invoice resending fixes, BCC notifications |
| 3.1.03–3.2.05 | Charging logic, refund processing, Finance System integration |
| 3.0.06–3.1.02 | Per-head cost calculations and invoice generation |
