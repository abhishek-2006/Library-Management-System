# 📚 Library Management System (LMS)

A modern web-based **Library Management System** designed to simplify and automate core library operations like book issuance, cataloging, and request tracking.  
Built with **PHP**, **MySQL (PDO)**, **TailwindCSS**, **HTML**, **CSS**, and **JavaScript** — following an organized structure for scalability and maintainability.

---

## ✨ Key Features & Recent Enhancements

### 🔹 1. Enhanced Borrow Request Management
A fully improved multi-step system for handling student borrow requests.

- **Borrow Request Table (`tblrequests`)** now uses string-based status tracking: `Pending`, `Approved`, `Rejected`.
- **Secure Direct Issuance:**  
  When a librarian clicks “Issue”, the system:
  - Updates `tblrequests.Status` → `'Approved'`
  - Decrements `tblbooks.bookCopies`
  - Inserts a record into `tblissuedbookdetails` (with `ExpectedReturnDate` based on system settings)
- **Smooth UI Flow:**  
  - Tailwind modals replace JavaScript alerts for confirmations.  
  - Book cover image is shown in the request list for instant recognition.

---

### 🔹 2. Refined New Book Request Workflow
Improved to match real-world procurement flow.

- **Three-Stage Status:**  
  1. **Pending:** Awaiting librarian review  
  2. **Approved:** Approved for purchase (does *not* add directly to `tblbooks`)  
  3. **Cataloged:** Book is added through `add-book.php` once it arrives
- **Smart Catalog Transfer:**  
  Approved requests pre-fill the **Add Book** form for easy cataloging.

---

## 💻 Tech Stack

| Layer | Technology Used |
| :--- | :--- |
| **Frontend** | HTML5, CSS3, JavaScript, TailwindCSS, Font Awesome |
| **Backend** | PHP (Server-side logic) |
| **Database** | MySQL/MariaDB (with PDO) |
| **Web Server** | Apache (XAMPP Recommended) |

---

## ⚙️ Installation Guide

### 🧩 Prerequisites
- **XAMPP / WAMP / LAMP**  
- **PHP 7.4+**  
- **MySQL or MariaDB**

### 🚀 Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/abhishek-2006/Library-Management-System.git
   ```

2. **🗂️ Move Project Files**
   Place the project folder into your web server root (for XAMPP):
   ```bash
   C:\xampp\htdocs\Library Management System
   ```

---

## 🧩 Database Setup

Open **phpMyAdmin**

Create a new database named:

```sql
library
```

Import the SQL file from:

```bash
sql file/library.sql
```

---

## ⚙️ Configure the Database Connection

Open:

```bash
library/includes/config.php
```

Update credentials if needed:

```php
$dbh = new PDO("mysql:host=localhost;dbname=library", "root", "");
```

---

## 🚀 Run the Application

Open your browser and go to:
👉 [http://localhost/Library%20Management%20System/library/](http://localhost/Library%20Management%20System/library/)

---

### 🔑 Admin Login

**Username:** admin  
**Password:** admin

---

## 👨‍💻 Author

Made with ❤️ by **Abhishek Shah**

Give this project a ⭐ if you found it useful.

---

## 📄 License

This project is licensed under the **MIT License**.
