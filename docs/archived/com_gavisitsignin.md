# Attendance Register (com_gavisitsignin)

> **Archived** — this extension is not in the maintained repository. Documentation is preserved from Glenn Arkell's website.

## Description

A component for recording attendance for members and/or visitors, with QR code name badge support for streamlined sign-in.

## Key features

- Name badges with QR codes for member sign-in
- Admin view shows all attendees; member view shows own records
- Visitor tracking
- AES-256-CBC data encryption with configurable key
- Automatic sign-out for members who forget to log out
- CSV export
- Group highlighting (e.g. Committee)
- Member exclusion filters for overseas or non-attending members

## Configuration options

| Option | Description |
|--------|-------------|
| QR code image location | Where QR code images are stored |
| Visitor sign-in | Enable/disable visitor tracking |
| Default group | Group shown in attendance and badge listings |
| Encryption key | Key used for AES-256 data encryption |
| Auto sign-out time | Minutes until automatic sign-out |
| CSV export location | Where extract files are saved |
| Group highlighting | Highlight special members (e.g. Committee) |
| MIA exclusion | Exclude overseas/non-attending members from MIA list |

## Downloads *(may be outdated)*

- `com_gavisitsignin-5.3.0.zip` (J5–6)

> **Important:** On install, set the permissions of your membership user group to be allowed to **create**.
