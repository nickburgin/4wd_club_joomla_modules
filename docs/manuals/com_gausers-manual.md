# User Membership System Manual

*Converted from Glenn Arkell's original PDF manual.*

| Date | Author | Changes |
|------|--------|---------|
| 2023-08-03 | Glenn Arkell | Initial draft |

---

## Overview

The User Membership component assists with managing Core User records as well as membership invoicing.

This component was originally created to assist a club to manage and maintain membership records for historical and insurance purposes. It started as a relatively simple component but over time has been enhanced to cater for more functionality.

---

## Manage Members Listing

The management view shows a general list of member records with columns for Name, Address, Phone, and membership type/expiry date.

When an ordinary member views the list:
- **Name** is a clickable link that opens the member's email address in your mail client
- **Address** appears as a link only for the logged-in user's own record (or for the Membership Officer)
- **Phone** is a clickable link that opens the phone app

You can filter the listing by typing a partial name, email address, or phone number into the Search field. The filter button also allows filtering by membership type.

---

## Editing Your Own Record

Clicking your own address link opens your record in edit mode. There are two tabs:

### Membership tab

Fields include Member Name, Member Email, Member Phone, Member's Image (head & shoulder photo or avatar, max 250 KB), and Newsletter by Email toggle.

### Contact tab

Fields include Address 1, Address 2, Suburb, State/Territory, Postcode, Country, and Use Postal toggle.

Additional tabs may be present depending on which profile plugins are installed (e.g. [plg_user_profileb4wdc](../extensions/plg_user_profileb4wdc.md)).

---

## Display of Member's Record

After saving, the member's record is displayed with additional information:

- Joined date and last login
- Member ID
- Membership type
- Contact details
- Past invoices table (Invoice ID, Membership Expiry, Membership Type, Status, Amount, Date Paid)

Clicking an invoice number opens the invoice PDF.

---

## Administration of Members

When a Membership Officer views the listing, an **Actions** column appears with icon buttons for each member:

| Icon | Action |
|------|--------|
| Green tick / Red cross | Toggle member enabled/disabled (financial status) |
| Right arrow exiting doorway | Mark as left the club — moves to the Left Club user group and deactivates |
| Sad face | Mark as deceased — set date of death (useful for an honour roll) |
| Envelope | Send welcome email to member |
| Invoice | Generate an invoice for this member |
| Search | Open the member's details page |

### Editing a member

Membership Officers can edit any member's record by clicking the address link or the search icon.

The display view for an officer also shows a **Member's History** section and an **Action** button for recording notable events (e.g. "10 Year Badge Issued", life membership nomination).

### Creating a new member

Click the **+ New Member** button at the bottom of the listing. When the form is submitted:

1. The email address is checked for uniqueness
2. A new Joomla user record is created with an auto-generated password
3. Joomla's standard new user email is sent with username and password
4. A membership invoice PDF is created and emailed to the new member
5. A welcome note is emailed if one is configured

---

## Membership Invoices

When viewing the Membership Invoices listing you can click **Generate Invoices** (green button) to create invoices for all current members.

> The Generate Invoices button only appears at a date 10 months after the last annual invoices were created. This is controlled by the **Last Invoice Start Date** configuration parameter.

The invoice listing shows columns: Member Name (links to PDF), Amount, End Date, Date Paid, and action buttons:

| Icon | Action |
|------|--------|
| Envelope | Resend PDF invoice to member |
| Credit card | Mark invoice as paid |
| Rubbish bin | Cancel invoice |

**Colour coding:** black = user disabled; green = user enabled; red = user has no email address (needs postal mail).

The **Disable Users** and **Reinstate Users** buttons allow bulk management of members with unpaid invoices.

### Marking as paid

Clicking the credit card icon opens a modal to enter the date paid and confirm the amount. If left blank, today's date is used. Once marked paid, the record disappears from the unpaid list.

If com_gafinance is installed and configured, a finance record for the payment is created automatically.

---

## Application Form

When the **Member Application** option is enabled in configuration, you can create a Joomla menu item linking to an online application form.

When submitted, no database record is created. Instead, a PDF application form is generated and emailed to the prospective member. They complete it (select membership type, sign), then scan/photograph and return it to the Membership Officer, who enters the details as a new member.

---

## Administration & Configuration

Access the component dashboard via **Joomla Admin → Components → User Memberships**. The dashboard links to Invoices, Committee, Membership Types, Audits, and Configuration Options.

### Membership Types

Create membership types before using the component. Each type has:
- Title
- Joining Fee
- Subscription
- Discount
- Term Type (YEAR, MONTH, LIFE, etc.)
- Term Number (e.g. 1 for annual)

### Audits

Read-only log of all actions taken on user records. Accessible to System Administrators only.

### Committee

Records current and historical committee members. Positions can be renamed via Joomla's language override. Each committee record has a Term Expiry Date — create a new record when a new committee is elected.

---

## Configuration Options

### Base Parameters

| Parameter | Description |
|-----------|-------------|
| Exclude Email Prefix | Email prefix used for placeholder addresses (e.g. `noemail`) — these are silently skipped when sending emails |
| Profile Suffix | Suffix of the enhanced profile plugin (e.g. `b4wdc` for `plg_user_profileb4wdc`) |
| Image Location | Folder where member photos are stored |
| Hide Vax | Hides COVID-19 vaccination fields (likely not needed) |

### Group Settings

| Parameter | Description |
|-----------|-------------|
| Group to Send To | User group to email or invoice |
| Temporary Member Group | Group for temporary members |
| Temporary Membership | Membership type for temporary members |
| Group to Exclude | Groups excluded from email sends |
| Left Club Group | Group assigned when a member leaves |
| Deceased Group | Group assigned when a member is deceased |
| Deceased Field | Profile field used as Date of Death |
| Training Group | Group for training coordinators |
| WWC Group | Group with authority to edit Working With Children details |
| Membership Secretary | Group with authority to edit member records |

### Finance

| Parameter | Description |
|-----------|-------------|
| Incorporate Finance | Whether to create finance records when invoices are paid (requires com_gafinance) |
| Finance Category ID | Finance transaction category to use for membership payments |
| Account Name | Bank account name (printed on invoices) |
| BSB / Account Number | Bank details (printed on invoices) |
| Allow PayPal | Whether PayPal payment is offered |
| PayPal Account | PayPal email address |
| PayPal Button | Image shown on invoice PDF for PayPal link |
| Admin User ID | User authorised to administer the component |
| Address / Suburb / Phone | Club contact details for invoices |

### Invoicing

| Parameter | Description |
|-----------|-------------|
| Exclude Membership# | Members excluded from bulk invoice generation (Ctrl+click to multi-select) |
| Last Invoice Start Date | Expiry date of the current invoice run — updated automatically when Generate Invoices is clicked |
| Use Cutoff Date | If yes, members joining before the cutoff date are invoiced through to the end of the following membership year |
| Invoice Due | Days until invoice is due (printed on PDF) |
| Description | Description line on invoice |
| Logo Image | Header image on invoice PDF |
| Invoice Footer | Footer text on invoice PDF |
| Invoice Location | Folder where PDF copies are stored |
| Order Invoices | Sort invoices by number or name |
| Individual Invoices | Show generate-invoice button per member in front-end listing |
| Disable Users | Automatically disable all members when new invoices are generated |
| Acknowledgement | Send a confirmation email when an invoice is marked as paid |
| Ack. Text | Confirmation email message text |
| Include This Text | Text appended to the invoice email |
| Link Site Name | Make the site name/logo on the invoice a link to the website |

### Membership

| Parameter | Description |
|-----------|-------------|
| M'ship Period | Financial Year or Calendar Year |
| New Members | Show New Member button in front-end listing |
| Auto Create Members | Purpose unclear — see source for current behaviour |
| Send Email | Send membership invoices by email |
| Default Membership | Default membership type when creating a new member |
| Welcome Article | Article emailed to new members as a welcome note |
| Discount Enabled | Enable membership discounts via a profile field |
| Exempt Membership | Membership types exempt from invoicing (e.g. Life) |
| Extend Expiry | Membership types whose expiry is extended rather than renewed |
| Exempt Group | User groups exempt from invoicing |
| Payment Message | Message text on invoice PDF below banking details |
| Allow Pro Rata | Enable pro-rata invoice calculation |
| Year Pro Rata Applies | First or second year of membership for pro-rata |
| Minimum Pro Rata Fee | Floor amount for pro-rata calculations |
| Temp Mbr Valid | Duration (months) of a temporary membership |

### Privacy Settings

| Parameter | Description |
|-----------|-------------|
| Privacy Field | Custom field or profile field used as a privacy toggle |
| Privacy Switch | The yes/no field that activates privacy for a member |
| Privacy Colour | Highlight colour in listing for members with privacy enabled |
| Allow Directory List | Show button to generate a Members Directory PDF |
| Allow Members List | Show button to generate a members CSV |
| Display Address | Show address in listing; Suburb Only option available |
| Include Years | Display years of membership |
| Include Partner | Display partner name; Joint Mship option combines names |
| Safe Files | Permitted file types for member images |
| Delete Image File | Whether deleting a member image also removes the physical file |

### Extra Profile Info

| Parameter | Description |
|-----------|-------------|
| Use Club Number | Include club number in member list extract |
| Extra Info to Include | Additional enhanced profile fields to display |
| Extract Members | Show a button to generate a CSV extract |
| Compare Members | Upload a CSV to compare against database records |
| Display Expiry | Show expiry date of last paid invoice |

#### Extract Members sub-settings

| Parameter | Description |
|-----------|-------------|
| Club ID | Club/group number for parent organisation |
| Financial Members Only | Include only financial members in extract |
| Extract Directory | Folder where extract file is saved |
| Email Address | Email address the extract is sent to |
| Extract Type | CSV or SQL |
| Profile Fields | Additional profile fields to include |
| Exclude from Extract | Specific members to exclude |
| Extract Number | Counter incremented with each extract |
| Last Extract Date | Date of the most recent extract |

### Applications

| Parameter | Description |
|-----------|-------------|
| Member Application | Enable the online application form |
| Ignore Mships | Membership types excluded from the application form |
| Heading | Heading text on the PDF application form |
| Application Image | Header image on the PDF application form |
| Header Text | Text in the header section of the PDF |
| Footer Text | Text in the footer section of the PDF |
| Signature Required | Whether a signature block appears on the form |

### Address List

| Parameter | Description |
|-----------|-------------|
| Member's Addresses | Show Address List button in front-end listing |
| User Groups | User group to include in the address list |
| Email List | Automatically email the list to the requesting member |

### Integration

| Parameter | Description |
|-----------|-------------|
| Test Email | Enable test mode — set a specific user as the test recipient |
| Block Email | Disable all outgoing emails (all other processes still run) |
| Use BarCode | Enable barcode/QR code functionality |
| Edit Custom Fields | Allow editing of custom fields within the component |
| Use Mail Template | Use a Joomla mail template for outgoing emails |
| Log Actions | Log a user action every time any member edits anything (WARNING: high disk usage) |
| Log to Mbr Secretary | Email Membership Secretary when a member makes changes |
| Enable Versions | Enable Joomla's version history for records |

### Permissions

Standard Joomla ACL permissions plus component-specific actions:

| Action | Description |
|--------|-------------|
| Manage Memberships | Ability to manage member records and invoices |
| Manage Training | Access to training coordinator functions |
| Upload Files | Ability to upload files to member records |
| Members Directory | Ability to generate the members directory PDF |
| Review Audits | Access to the audit trail |

The Membership Officer group should typically be allowed **Create**, **Delete**, **Edit**, **Edit State**, **Edit Own**, **Manage Memberships**, **Upload Files**, **Members Directory**, and **Review Audits**.
