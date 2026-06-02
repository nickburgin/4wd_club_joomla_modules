# Calendar Events (com_gacalevents)

**Package:** `pkg_gacalevents` (includes `mod_gacalevents`)  
**Current version:** 3.3.1 (package 4.1)

## Description

A club calendar component for managing events and tracking attendance. Includes a front-end module for displaying upcoming events.

## Installation

Install `pkg_gacalevents` via **Joomla Admin → Extensions → Install → Upload Package File**.

The package includes:
- `com_gacalevents` — the calendar events component
- `mod_gacalevents` — front-end upcoming events module

## Key features

- Event calendar with month groupings
- Event detail pages (title, description, date/time, departure point, leader/contact)
- RSVP and apology management per event
- Attendance download (CSV)
- Event reminders to configurable user groups
- Repeating events
- Formal events with attendance categories
- Per-head charging with invoice generation
- Email notifications for event updates and new events (BCC)
- Partner/membership integration
- Activity logging

## Configuration options

### Display

| Parameter | Description |
|-----------|-------------|
| Header Title | Page title for the events listing |
| Header Text | Introductory text blocks (two fields) |
| Colours | Background and heading text colours for calendar display |
| Contact Label | Custom label for the contact/leader field |
| Location Label | Custom label for the departure/location field |
| Coordinator Label | Custom label for the coordinator/trip leader role |
| Event Email | Contact email address shown on event pages |

### Functionality

| Parameter | Description |
|-----------|-------------|
| Allow Apology | Enable apology responses in addition to attendance confirmations |
| Show Editor | Show the event editor interface to eligible users |
| Editor Group | User group permitted to create/edit events |
| Reminders | Enable event reminders |
| Reminder Group | User group eligible to receive reminders |
| Notify on Attendance | Send notifications when attendance changes |
| Activity Logging | Log attendance record activity |
| Download Attendance | Allow downloading the attendance list |
| Formal Events | Enable formal event mode with attendance categories |
| Authorised Users | Specific users approved to create events |
| Ignore Users | Users excluded from event notifications |
| Duplicate Prevention | Window (days) within which duplicate events are blocked |

### Repeating events

| Parameter | Description |
|-----------|-------------|
| Repeat Event | Enable event repetition |
| Repeat Quantity | How many times to repeat |
| Repeat Type | Interval unit (days, weeks, months) |

### Charging

| Parameter | Description |
|-----------|-------------|
| Charge for Events | Enable per-head event charging |
| Send Invoice | Generate invoices for charged events |

### Membership integration

| Parameter | Description |
|-----------|-------------|
| Partner Membership | Enable partner/membership integration |
| Profile Suffix | Profile plugin suffix for partner field lookup |

## Integration

- **[com_gafinance](com_gafinance.md):** Optional integration for tracking event charge payments.
- **[plg_user_profileb4wdc](plg_user_profileb4wdc.md):** Partner name lookup uses the configured profile suffix.

## Version history

| Version | Notes |
|---------|-------|
| 3.3.1 (pkg 4.1) | Current release |
