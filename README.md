# 📦 Order Tracking System

![MySQL](https://img.shields.io/badge/MySQL-8.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.x-purple.svg)
![Apache](https://img.shields.io/badge/Apache-Server-red.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-4-blueviolet.svg)
![License](https://img.shields.io/badge/License-Academic-green.svg)

A full-stack **web-based Order Tracking System** for managing deliveries, real-time shipment tracking, and logistics operations. Built with a normalized MySQL database (3NF), Apache server, and a responsive Bootstrap UI.

> 🎓 Database Systems Project — Team 12  
> Submitted: December 2024

---

## 👥 Team

| Name | Student ID |
|------|------------|
| Manohar Kota | 002836112 |
| Bhagya Teja Chalicham | 002847156 |
| Sai Sarvagna Beeram | 002817491 |

---

## 📌 Overview

The Order Tracking System enables end-to-end logistics management with:
- **Role-based access** for Admins, Staff, and Customers
- **Real-time parcel tracking** using unique tracking IDs
- **Secure authentication** with MD5 hashed passwords
- **Report generation** for operational insights
- A fully normalized **MySQL 8.0** database in **3rd Normal Form (3NF)**

---

## 🗃️ Database Design

### Entities & Relationships

| Entity | Primary Key | Relationships |
|--------|------------|---------------|
| Customer | customer_id | 1:N → Order |
| Order | order_id | 1:N → Parcel, 1:1 → Shipment |
| Parcel | parcel_id | N:1 → Order |
| Staff | staff_id | N:1 → Branch |
| Branch | branch_id | 1:N → Staff |
| Shipment | shipment_id | 1:N → Tracking, 1:N → Report |
| Tracking | tracking_id | N:1 → Shipment |
| Report | report_id | N:1 → Shipment |

### Normalization
The database achieves **Third Normal Form (3NF)**:
- All non-key attributes depend only on the primary key
- No transitive dependencies
- Foreign keys enforce referential integrity

---

## 🚀 Features

### Admin Panel
- Manage branches (add/edit/delete)
- Manage staff members
- Add and track parcels
- View all shipment statuses
- Generate and print reports

### Staff Panel
- View and update assigned parcels
- Update parcel status (Collected → Shipped → In-Transit → Delivered)

### Customer/User Panel
- Register and login
- Track parcels using a unique tracking number
- View shipment history and reports

### Parcel Status Flow
```
Item Accepted by Courier → Collected → Shipped → In-Transit
→ Arrived at Destination → Out for Delivery → Delivered / Picked-up
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 7.x |
| Database | MySQL 8.0 |
| Server | Apache (XAMPP/WAMP) |
| Frontend | HTML5, CSS3, Bootstrap 4 |
| JavaScript | Vanilla JS, Ajax, jQuery |
| Security | MD5 password hashing |
| Reporting | PHP-generated reports (printable) |

---

## 📂 Project Structure

```
order-tracking-system/
│
├── 📄 README.md
├── 📄 .gitignore
│
├── 📁 database/
│   ├── schema.sql          → Full DB schema (CREATE TABLE statements)
│   └── sample_data.sql     → Sample data for testing
│
├── 📁 src/
│   ├── config.php          → DB connection config
│   ├── auth.php            → Login/logout/session handling
│   ├── admin/              → Admin panel pages
│   ├── staff/              → Staff panel pages
│   └── user/               → Customer/user pages
│
└── 📁 docs/
    └── er_diagram.png      → ER Diagram
```

---

## ⚙️ Setup & Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) or WAMP (Apache + MySQL + PHP)
- MySQL 8.0+
- PHP 7.x+

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/your-username/order-tracking-system.git
cd order-tracking-system
```

**2. Move to web server directory**
```bash
# For XAMPP (Windows)
cp -r order-tracking-system/ C:/xampp/htdocs/

# For XAMPP (Linux/Mac)
cp -r order-tracking-system/ /opt/lampp/htdocs/
```

**3. Import the database**
- Open **phpMyAdmin** → `http://localhost/phpmyadmin`
- Create a new database: `order_tracking_db`
- Import `database/schema.sql`
- Import `database/sample_data.sql`

**4. Configure DB connection**
Edit `src/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'order_tracking_db');
```

**5. Run the application**
Open browser → `http://localhost/order-tracking-system`

---

## 🔐 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@admin.com | admin123 |
| Staff | teja@sample.com | password |
| User | Register via signup page | — |

---

## 📊 Database Schema Summary

```sql
-- Key Tables
customers (customer_id PK, user_name, email, phone, address)
orders    (order_id PK, customer_id FK, status ENUM, order_date)
parcels   (parcel_id PK, order_id FK, dimensions, weight, parcel_type ENUM)
staff     (staff_id PK, branch_id FK, staff_name, email)
branches  (branch_id PK, branch_name, branch_phone, branch_address)
shipments (shipment_id PK, order_id FK, route_id FK, status ENUM, shipment_date)
tracking  (tracking_id PK, shipment_id FK, location, timestamp)
reports   (report_id PK, shipment_id FK, date, sender, recipient, amount, status)
```

---

## 🔭 Future Scope

- **Multi-Factor Authentication (MFA)** for enhanced security
- **IoT Integration** for real-time GPS-based tracking
- **Mobile App** (Android/iOS) for customers and admins
- **AI-Driven Route Optimization** to reduce delivery times and costs

---

## 📄 License

This project was developed for academic purposes as part of a Database Systems course. All rights reserved by the respective authors.
