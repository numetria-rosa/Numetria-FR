# Numetria-FR: Advanced Accounting System for Travel Agencies & B2B Wholesalers

## Overview
Numetria-FR is a robust, web-based accounting and management system designed for travel agencies and B2B travel booking wholesalers. It streamlines the management of agencies, reservations, financial operations, and reporting, supporting multi-currency and multiple booking types (hotels, flights, activities, and more).

## Features
- **Agency Management**: Add, update, and view detailed agency profiles, including credit, guarantees, and compliance documents.
- **Reservation Management**: Handle bookings for hotels, flights, and activities with support for multiple suppliers and service types.
- **Financial Operations**:
  - Track credits (réglements), deposits, and guarantees per agency.
  - Manage payments, outstanding balances, and payment statuses.
  - Archive and audit financial transactions and bookings.
- **Multi-Currency Support**: Agencies and bookings can operate in different currencies.
- **User Authentication**: Secure login for accountants and agency staff.
- **Reporting & Dashboard**: Visual dashboard for quick insights into balances, credits, debits, and agency performance.
- **Data Filtering & Export**: Filter reservations and financial data by agency, date, and status. Export data as needed.
- **Responsive UI**: Modern, mobile-friendly interface using Bootstrap and DataTables.

## Main Modules
- **Dashboard**: Overview of system status and quick access to modules.
- **Agencies**: Manage travel agencies, view details, and handle compliance.
- **Reservations**: Manage and filter bookings (hotels, flights, activities, etc.).
- **Financials**: Handle credits, deposits, guarantees, and payment reconciliation.
- **Archive**: Store and review historical booking and payment data.
- **Etat**: Generate and view financial statements and balances.

## Booking Types Supported
- Hotels (multiple suppliers)
- Flights (with PNR and itinerary management)
- Activities (with supplier and modality details)

## Technology Stack
- **Backend**: PHP (MySQLi)
- **Frontend**: HTML5, CSS3, Bootstrap, DataTables, jQuery
- **Database**: MySQL/MariaDB (see `demo_standard.sql` for schema)
- **Node.js (for export features)**: Uses `sheetjs-style` and `xlsx-js-style` for Excel export

## Installation & Setup
1. **Clone the repository**
2. **Database Setup**:
   - Import `demo_standard.sql` into your MySQL/MariaDB server.
   - Update `files/db_connection.php` with your database credentials.
3. **Web Server**:
   - Deploy the PHP files on an Apache/Nginx server with PHP 7.4+.
   - Ensure the `assets/` directory is accessible.
4. **Node.js (Optional, for Excel export)**:
   - Run `npm install` to install Node.js dependencies (`sheetjs-style`, `xlsx-js-style`).

## Usage
- Access the system via your web browser at the server's URL.
- Login with your user credentials (see `invoiceuser` table for demo users).
- Use the sidebar to navigate between Dashboard, Agencies, Reservations, Financials, Archive, and Etat.
- Add, update, and filter data as needed. Export features are available for reporting.

## Dependencies
- PHP 7.4+
- MySQL/MariaDB
- Node.js (for export features)
- [sheetjs-style](https://www.npmjs.com/package/sheetjs-style)
- [xlsx-js-style](https://www.npmjs.com/package/xlsx-js-style)
- Bootstrap, DataTables, jQuery (included in `assets/`)

## Demo Data
- Use the provided `demo_standard.sql` to populate the database with sample agencies, bookings, and financial records.

## License
- Please add a LICENSE file to specify the terms of use.

## Screenshots
_Add screenshots of the dashboard and main modules here._

## Contact
For support or inquiries, please contact the project maintainer. 
