# Quick Setup Guide

## For XAMPP/WAMP (Localhost)

### 1. Database Setup
1. Start Apache and MySQL in XAMPP/WAMP
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create new database: `car_service_portal`
4. Import `database/schema.sql`

### 2. Configure Database
Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty
define('DB_NAME', 'car_service_portal');
```

### 3. Access Application
- URL: `http://localhost/final_project/public/index.php`
- Or set document root to `public` folder and use: `http://localhost/index.php`

## For Cloud Hosting

### 1. Upload Files
Upload all files to your hosting account maintaining the folder structure.

### 2. Database Setup
1. Create MySQL database in your hosting control panel
2. Note the database credentials (host, username, password, database name)
3. Import `database/schema.sql` using phpMyAdmin or command line

### 3. Configure Database
Edit `config/db.php` with your cloud MySQL credentials:
```php
define('DB_HOST', 'your-cloud-host.com');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');
define('DB_NAME', 'your-database-name');
```

### 4. Path Configuration
If your document root is the `public` folder:
- All paths starting with `/public/` should be changed to `/`
- Or use relative paths

## Important Notes

1. **Password Security**: Change default passwords in production
2. **Error Reporting**: Disable error display in production (set `display_errors = Off` in php.ini)
3. **File Permissions**: Ensure PHP can write to session directory
4. **HTTPS**: Use HTTPS in production for security

## Testing

1. Visit the home page
2. Register a new account or login with demo accounts
3. Test booking flow as customer
4. Login as garage owner and manage services
5. Login as admin and manage users/garages

## Demo Accounts

All use password: `password123`
- Admin: admin@carservice.com
- Garage Owner: garage1@example.com
- Customer: customer1@example.com
