# 4WD User Profile Plugin (plg_user_profileb4wdc)

**Current version:** 5.3

## Description

A Joomla user plugin that adds specialised data fields to user profiles tailored for Four Wheel Drive clubs. Integrates with [com_gausers](com_gausers.md) and is referenced by the `Profile Suffix` setting in that component's configuration.

## Installation

Install `plg_user_profileb4wdc` via **Joomla Admin → Extensions → Install → Upload Package File**.

After installation, enable the plugin via **Joomla Admin → Extensions → Plugins → User - Profile Enhancement 4WD**.

Then set the **Profile Suffix** in `com_gausers` configuration (Base Parameters tab) to `b4wdc`.

## Features

- Adds 4WD-specific custom fields to user profiles
- Fuel type field includes electric vehicle option
- Working With Children (WWC) details with configurable edit permissions
- Audit trail linked to User Management System
- Membership Secretary change notifications

## Version history

| Version | Notes |
|---------|-------|
| 5.3 | Adds fuel type option for electric vehicles |
| 4.0.10–4.5.2 | Substantial modifications to User Management System integration |
| 4.0.09 | Membership Secretary role change notifications |
| 4.0.05 | Configurable WWC editing permissions |
| 4.0.00 | Migration to Joomla 4; audit trail functionality |
