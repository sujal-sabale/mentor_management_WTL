# 🎓 MentorSphere — Mentor & Mentee Management System

[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%20%7C%208.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![CSS3](https://img.shields.io/badge/CSS3-Modern%20UI-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://www.w3.org/Style/CSS/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

A full-stack, responsive **Mentor & Mentee Management System** built for academic institutions and organizations. It streamlines faculty-student mentorship allocation, tracks mentor capacities, manages profiles with photo uploads, and provides real-time analytics.

---

## 🌟 Key Features

- 👨‍🏫 **Complete Mentor Management (CRUD)**:
  - Add, view, edit, and delete mentor profiles with department, designation, and employee ID.
  - Set custom maximum mentee capacity per mentor.
  - Image upload support (JPG, PNG, WebP) with validation and cleanup.

- 🎓 **Complete Mentee Management (CRUD)**:
  - Enroll students with Roll No/Student ID, academic year, department, email, and phone.
  - Real-time mentor allocation with capacity validation (prevents over-allocation).
  - Track unassigned vs. assigned mentees effortlessly.

- 📊 **Real-time Analytics Dashboard**:
  - Live statistics cards for total mentors, total mentees, allocated mentees, and unassigned students.
  - Capacity progress indicators showing mentorship utilization.

- 🔍 **Interactive Search & Filtering**:
  - Instant live search by name, ID, department, or designation.
  - Department and status filtering.

- ⚡ **Dual Environment Support (Auto-Detect)**:
  - Seamless auto-switching database configuration in [`db.php`](db.php) for **Localhost (XAMPP)** and **InfinityFree Live Cloud Hosting**.

- 🛡️ **Security & Reliability**:
  - SQL Injection prevention via PHP **PDO Prepared Statements**.
  - Server-side and client-side form validation.
  - Automatic old profile photo cleanup upon update or deletion.

---

## 🗂️ Project Structure

```
mentor_management_WTL/
├── api.php                   # REST API handling Mentor & Mentee CRUD operations
├── db.php                    # Database connection with environment auto-detection
├── deploy_to_xampp.bat       # Quick batch script to deploy files to XAMPP htdocs
├── index.html                # Single Page Application frontend (HTML5 / Modern CSS / Vanilla JS)
├── index.php                 # Entry redirector / index fallback
├── README.md                 # Project documentation
├── schema.sql                # MySQL schema for local development (mentors table)
├── schema_infinityfree.sql   # Full relational schema (mentors + mentees + sample data)
├── style.css                 # Custom styling and design tokens
└── uploads/                  # Upload directory for profile pictures (.gitkeep included)
```

---

## 💾 Database Schema

The database consists of two relational tables: `mentors` and `mentees`.

### 1. `mentors` Table
| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Unique mentor ID |
| `name` | `VARCHAR(150)` | `NOT NULL` | Full name of mentor |
| `employee_id` | `VARCHAR(50)` | `NOT NULL`, `UNIQUE` | Unique faculty identifier |
| `department` | `VARCHAR(100)` | `NOT NULL` | Department / Discipline |
| `designation` | `VARCHAR(100)` | `NOT NULL` | E.g., Professor, Associate Prof |
| `max_mentees` | `INT` | `NOT NULL` | Maximum capacity allowed |
| `photo_path` | `VARCHAR(255)` | `DEFAULT NULL` | Relative file path to photo |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Record timestamp |

### 2. `mentees` Table
| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `AUTO_INCREMENT`, `PRIMARY KEY` | Unique mentee ID |
| `name` | `VARCHAR(150)` | `NOT NULL` | Full name of student |
| `student_id` | `VARCHAR(50)` | `NOT NULL`, `UNIQUE` | Roll Number / Student ID |
| `department` | `VARCHAR(100)` | `NOT NULL` | Student department |
| `academic_year` | `VARCHAR(50)` | `NOT NULL` | FE / SE / TE / BE |
| `email` | `VARCHAR(150)` | `DEFAULT NULL` | Student email address |
| `phone` | `VARCHAR(30)` | `DEFAULT NULL` | Contact number |
| `mentor_id` | `INT` | `FOREIGN KEY` &rarr; `mentors(id)` | Assigned mentor ID |
| `photo_path` | `VARCHAR(255)` | `DEFAULT NULL` | Relative file path to photo |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Record timestamp |

---

## 🚀 Getting Started (Local Setup with XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 7.4 or 8.x)
- Web browser (Chrome, Edge, Firefox, Safari)

### Installation Steps

1. **Clone the repository**:
   ```bash
   git clone https://github.com/sujal-sabale/mentor_management_WTL.git
   ```

2. **Move to XAMPP htdocs**:
   Copy the project folder into your XAMPP web directory:
   ```
   C:\xampp\htdocs\mentor_management\
   ```
   *(Or run [`deploy_to_xampp.bat`](deploy_to_xampp.bat) if configured)*.

3. **Start Apache and MySQL**:
   - Open **XAMPP Control Panel**.
   - Start both **Apache** and **MySQL** services.

4. **Import Database**:
   - Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database named `mentor_management` (or import [`schema_infinityfree.sql`](schema_infinityfree.sql) directly).
   - Click **Import** &rarr; Select [`schema_infinityfree.sql`](schema_infinityfree.sql) &rarr; Click **Go**.

5. **Run the Application**:
   - Open your browser and navigate to:
     ```
     http://localhost/mentor_management/
     ```

---

## 🌐 Live Cloud Hosting (InfinityFree / cPanel)

1. Upload all project files to `htdocs/` using FTP or File Manager.
2. Create a MySQL Database in your hosting control panel.
3. Import [`schema_infinityfree.sql`](schema_infinityfree.sql) via phpMyAdmin.
4. Update your database credentials in [`db.php`](db.php) under the hosting section:
   ```php
   $host     = 'sqlXXX.infinityfree.com';
   $dbname   = 'your_database_name';
   $username = 'your_database_username';
   $password = 'your_account_password';
   ```

---

## 📡 API Reference

All requests are dispatched to [`api.php`](api.php) via `GET` / `POST` query parameters or form data.

### Mentors Endpoints
| Action | Method | Description |
| :--- | :--- | :--- |
| `?action=read` | `GET` | Fetch all mentors with mentee counts |
| `?action=create` | `POST` | Add a new mentor profile (`multipart/form-data`) |
| `?action=update` | `POST` | Update mentor details and profile image |
| `?action=delete` | `POST` | Delete mentor and remove stored image |

### Mentees Endpoints
| Action | Method | Description |
| :--- | :--- | :--- |
| `?action=read_mentees` | `GET` | Fetch all mentees with mentor assignment details |
| `?action=create_mentee` | `POST` | Enroll new mentee with capacity validation |
| `?action=update_mentee` | `POST` | Update mentee profile & mentor allocation |
| `?action=delete_mentee` | `POST` | Delete mentee record and clean profile photo |

---

## 🛠️ Technology Stack

- **Frontend**: Semantic HTML5, Vanilla JavaScript (ES6+ async/await & Fetch API), Modern CSS3 (CSS Variables, Flexbox/Grid, Glassmorphism).
- **Backend**: PHP 7.4 / 8.x with PDO extension.
- **Database**: MySQL / MariaDB with Foreign Key relationships and cascading actions.
- **Typography & Icons**: Google Fonts ([Inter](https://fonts.google.com/specimen/Inter)) & SVG Icons.

---

## 📄 License

This project is created for academic and educational purposes under the **WTL Course Curriculum**. Distributed under the MIT License.