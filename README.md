# Fashion Management System

A professional PHP-based fashion management system with separate portals for admin, customers, and suppliers.

## 📁 Project Structure

```
fashion-php/
├── config/                  # Configuration files
│   └── database.php        # Database connection settings
│
├── public/                 # Public entry point (web root)
│   ├── index.php          # Main landing page
│   ├── about.html         # About page
│   ├── login.html         # General login page
│   ├── page.html          # Additional page
│   └── assets/            # Static assets
│       ├── css/           # Stylesheets
│       │   ├── style.css
│       │   ├── login.css
│       │   ├── about.css
│       │   └── button.css
│       └── images/        # Image files
│
├── modules/               # Application modules
│   ├── admin/            # Admin module
│   │   ├── login.html
│   │   └── login.php
│   │
│   ├── customer/         # Customer module
│   │   ├── login.html
│   │   ├── login.php
│   │   ├── register.html
│   │   ├── registration.php
│   │   ├── profile.php
│   │   ├── details.php
│   │   ├── update.html
│   │   ├── update.php
│   │   └── delete.php
│   │
│   └── supplier/         # Supplier module
│       ├── login.html
│       ├── login.php
│       ├── register.html
│       ├── registration.php
│       ├── details.php
│       ├── update.html
│       ├── update.php
│       └── delete.php
│
├── includes/             # Shared utilities and helpers
│
└── Log/                  # Legacy log files (to be migrated)
```

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser

### Installation Steps

1. **Clone or download the project**
   ```bash
   cd d:\ProjectFiles\fashion-php
   ```

2. **Configure the database**
   - Create a MySQL database named `fashion`
   - Update database credentials in `config/database.php`:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "your_password";
     $db = "fashion";
     ```

3. **Import database schema**
   - Create necessary tables:
     - `admin`
     - `customer_login_details`
     - `supplier_login_details`

4. **Configure web server**
   - Set document root to `public/` directory
   - Enable mod_rewrite (for Apache)
   - Configure PHP to allow file uploads if needed

5. **Set permissions**
   ```bash
   # Linux/Mac
   chmod -R 755 fashion-php/
   chmod -R 775 fashion-php/public/assets/
   ```

## 🌐 Access Points

- **Home Page**: `http://localhost/index.php`
- **Admin Login**: `http://localhost/../modules/admin/login.html`
- **Customer Portal**: `http://localhost/../modules/customer/login.html`
- **Supplier Portal**: `http://localhost/../modules/supplier/login.html`

## 📋 Professional Naming Conventions

All files now follow professional naming standards:
- ✅ **Lowercase filenames** - No uppercase characters in filenames
- ✅ **No spaces** - Using underscores or hyphens where needed
- ✅ **Descriptive names** - Clear purpose indicated by filename
- ✅ **Consistent patterns** - Same naming across all modules
- ✅ **Module organization** - Files grouped logically by functionality

## 🔒 Security Features

- Prepared statements to prevent SQL injection
- Session management for authentication
- Password validation
- Input sanitization (to be enhanced)

## 📝 Development Guidelines

### Adding New Features
1. Place module-specific code in the appropriate `modules/` subdirectory
2. Shared utilities go in `includes/`
3. Database connections must use `config/database.php`
4. Static assets go in `public/assets/`

### Code Standards
- Use PSR-12 coding standards
- Always use prepared statements for database queries
- Implement proper error handling
- Add comments for complex logic
- Never store passwords in plain text (implement password_hash())

### Path References
- Use `__DIR__` for relative includes
- Database config: `include_once __DIR__ . '/../../config/database.php';`
- Always use absolute paths when possible

## 🛠️ Technologies Used

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS
- **Server**: Apache/Nginx

## 📊 Database Schema

### Tables Required
```sql
-- Admin table
CREATE TABLE admin (
    Admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Customer login details
CREATE TABLE customer_login_details (
    sid INT PRIMARY KEY AUTO_INCREMENT,
    uname VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Supplier login details
CREATE TABLE supplier_login_details (
    sid INT PRIMARY KEY AUTO_INCREMENT,
    uname VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);
```

## 🔄 Future Improvements

- [ ] Implement password hashing (password_hash/password_verify)
- [ ] Add CSRF protection
- [ ] Implement session timeout
- [ ] Add input validation and sanitization
- [ ] Create proper error logging system
- [ ] Implement email verification
- [ ] Add password reset functionality
- [ ] Create admin dashboard
- [ ] Implement role-based access control (RBAC)
- [ ] Add API endpoints for mobile apps
- [ ] Implement caching mechanism
- [ ] Add unit tests

## 📞 Support

For issues or questions, please contact the development team.

## 📄 License

Proprietary - All rights reserved

---

**Version**: 2.0.0  
**Last Updated**: January 3, 2026
