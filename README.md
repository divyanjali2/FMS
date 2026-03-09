# Fleet Management System (FMS)

A Laravel-based Fleet Management System to manage vehicles, inspections, rentals, and procurement for fleet maintenance operations.

---

## Features

- **Vehicle Management**
  - Track vehicles, license expiry, and availability.
  - Dashboard for vehicles expiring soon (insurance, emission, revenue license).

- **Inspection & Garage Reports**
  - Submit inspection reports.
  - Post-check verification by Fleet Manager.
  - GM approval workflow.

- **Fleet Rentals**
  - Manage available vehicles and record rentals.
  - Prevent double booking of vehicles.
  - Automatically exclude rented or released vehicles.

- **Procurement & Accounting**
  - Manage inventory procurement for inspections.
  - Generate Purchase Orders (POs) per supplier.
  - Track status: `send_to_fleet`, `send_to_accountant`.

- **PDF Generation**
  - Landscape-oriented Purchase Orders and GRN.
  - Downloadable PDFs with company and supplier info.

- **User Roles & Permissions**
  - Admin, Fleet Manager, Garage, Accountant.
  - Conditional dashboards and workflow per role.

---

## Requirements

- PHP >= 8.1
- Laravel 12
- MySQL
- Composer
- `barryvdh/laravel-dompdf` for PDF generation

---

## Installation

1. **Clone the repository**
    ```bash
    git clone https://github.com/yourusername/fleet-management.git
    cd fleet-management
    ```

2. **Install dependencies**
    ```bash
    composer install
    ```

3. **Environment setup**
    ```bash
    cp .env.example .env
    ```
    - Update `.env` with your database credentials and app settings.

4. **Generate application key**
    ```bash
    php artisan key:generate
    ```

5. **Run migrations**
    ```bash
    php artisan migrate
    ```

6. **Seed sample data (optional)**
    ```bash
    php artisan db:seed
    ```

7. **Serve the application**
    ```bash
    php artisan serve
    ```
    - Access the app at `http://127.0.0.1:8000`

---

## Usage

- **Dashboard:** Vehicle expiry overview, inspections, and fleet statuses.
- **Fleet Rentals:** View and rent available vehicles.
- **Procurement:** Manage inventory and generate POs per supplier.
- **Inspection Workflow:** Submit post-checks, approve work, track status.

---

## PDF & Print

- PDFs generated via **Dompdf**.
- Purchase Orders and Goods Received Notes are landscape-oriented for clarity.
- Downloadable from the Accountant and Procument dashboard.

---

## Code Structure

- **Controllers:** Handle logic for vehicles, inspections, rentals, and procurements.
- **Models:** Represent database tables (Vehicle, Inspection, Rental, Procurement, etc.).
- **Views:** Blade templates for dashboards, forms, tables, and PDFs.
- **Routes:** Defined in `web.php` with prefixes for `garage-reports`, `fleet_post_checks`, `rentals`, etc.

---

Happy Coding!! ✨