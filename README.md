# 🐾 PetAdopt - Pet Adoption System

PetAdopt is a professional, full-stack web application designed to bridge the gap between animal shelters and pet lovers. The platform provides a secure environment for managing pet listings, shelter approvals, and user adoptions.

## 🌟 Key Features

- **Secure Authentication**: Advanced security using PHP's `password_hash` (Bcrypt) and `password_verify` to protect user data.
- **"Remember Me" Logic**: Persistent login functionality for 30 days using secure browser cookies.
- **Role-Based Access Control (RBAC)**:
  - **Admin**: Total system oversight and shelter approval.
  - **Shelter Member**: Manage pet profiles (Upload, Edit, Delete).
  - **Regular Member**: Browse and apply for pet adoptions.
- **Dynamic Pet Gallery**: A responsive grid system that fetches available pets from MySQL with automatic category-based styling (Dogs/Cats).
- **Session Analytics**: Tracks and calculates total time spent on the platform per session, displayed upon logout.
- **Approval Workflow**: Shelter accounts remain in a "Pending" state until verified by an administrator.

---

### 👥 Contribution

- **ISHRAR ISLAM** - [22-49436-3@student.aiub.edu](mailto:22-49436-3@student.aiub.edu)
- **Rifat Alam Chowdhury** - [22-48811-3@student.aiub.edu](mailto:22-48811-3@student.aiub.edu)

### 🎓 Supervision

This project was developed under the guidance and supervision of:

- **Supervisor**: [WAHIDUL ALAM RIYAD](mailto:wahid.riyad@aiub.edu)
- **Department**: Computer Science (CS)
- **Institution**: American International University-Bangladesh (AIUB)

## 🛠️ Tech Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML5, CSS
- **Environment**: XAMPP

---

## 📥 Local Installation & Setup

Follow these steps to get your development environment running:

### 1. Prerequisites

Ensure you have **XAMPP** installed with **PHP**.

### 2. Clone

Place the project folder into your XAMPP server directory:

### 3. Database Configuration

1.  Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2.  Go to **phpMyAdmin** (`http://localhost/phpmyadmin`).
3.  Create a new database named `pet_adopt_db`.
4.  Click **Import** and select the `db.sql` file located in the `/db` folder of this project.

### 4. Update Database Connection

Open `db/db.php` and verify your credentials match the following:

```php
<?php
$conn = mysqli_connect("localhost", "root", "", "pet_adopt_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>


```
