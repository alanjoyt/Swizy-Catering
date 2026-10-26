# Swizy - Catering Management System

Swizy is a modern PHP-based Catering Management System designed to streamline operations for catering services, manage bookings, allocate staff members automatically, handle billing, and provide smart menu recommendations.

## Features

- **Customer Dashboard**: Book events, view packages, build custom menus, track booking and payment status, download invoices, and submit feedback.
- **Smart Menu Recommendations**: Dynamic catering recommendations based on client preferences and guest size.
- **Admin Panel**: Manage menu items, packages, customers, staff members, bookings, and track reports.
- **Auto-Allocation System**: Programmatic allocation of Chefs, Servers, and Site Managers based on guest counts and staff availability.
- **Staff Portal**: View assignments, request leaves, and log task completion.

---

## Tech Stack

- **Backend**: PHP (Object-Oriented Database Access helper pattern)
- **Frontend**: HTML5, Vanilla CSS, JS (SweetAlert2, FontAwesome, Poppins Typography)
- **Database**: MySQL

---

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- MySQL 8.x or MariaDB
- Web Server (WampServer, XAMPP, Laragon, or Apache)

### Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/alanjoyt/Swizy-Catering.git
   cd Swizy-Catering
   ```

2. **Move to Web Root**:
   Place the project folder in your local server's web directory (e.g., `C:/wamp64/www/project/`).

3. **Import Database**:
   - Start your local MySQL server.
   - Create a database named `swizy`.
   - Import the schema and sample data from `database/swizy.sql`.

4. **Configuration**:
   - Copy `config/database.example.php` to `config/database.php`.
   - Update your database credentials in `config/database.php`:
     ```php
     $DB_HOST = "localhost";
     $DB_NAME = "swizy";
     $DB_USER = "root";
     $DB_PASSWORD = "";
     ```

5. **Run the Application**:
   Navigate to `http://localhost/project/` in your web browser.

---

## Project Documentation
- **Diagrams Guide**: Refer to [docs/catering_diagrams_guide.md](docs/catering_diagrams_guide.md) for UML Use Cases, Activity Diagrams, Sequence Diagrams, and Class Diagrams.
