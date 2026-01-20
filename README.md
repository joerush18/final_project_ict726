# Car Service & Garage Booking Portal

A complete web application for booking car services at garages, built with HTML5, CSS3, JavaScript, PHP, and MySQL.

## 🚀 Features

### For Customers
- User registration and authentication
- Browse and search garages
- View garage details and available services
- Book services with date/time selection
- Manage vehicles (add, view)
- View and manage bookings
- Cancel bookings

### For Garage Owners
- Login with garage owner role
- Manage services (Create, Read, Update)
- View all bookings for their garage
- Update booking status (approve, reject, complete)
- Filter bookings by status

### For System Admins
- Manage all users (activate/deactivate)
- Manage all garages (activate/deactivate)
- View system overview and statistics
- Access to all bookings

## 📁 Project Structure

```
final_project/
├── config/
│   └── db.php                 # Database configuration
├── includes/
│   ├── auth.php              # Authentication helper functions
│   ├── header.php            # Page header template
│   ├── footer.php            # Page footer template
│   └── navbar.php            # Navigation bar component
├── public/
│   ├── index.php             # Home page
│   ├── login.php             # Login page
│   ├── register.php          # Registration page
│   ├── logout.php            # Logout handler
│   ├── dashboard.php         # Role-aware dashboard
│   ├── garages.php           # Garage listing page
│   ├── garage_detail.php     # Garage details page
│   ├── book_service.php      # Booking form
│   ├── bookings.php          # Customer bookings list
│   ├── booking_detail.php    # Booking details
│   ├── cancel_booking.php    # Cancel booking handler
│   ├── vehicles.php          # Vehicle management
│   ├── privacy.php           # Privacy policy page
│   ├── garage_owner/
│   │   ├── services.php      # Service management (CRUD)
│   │   ├── bookings.php      # Garage bookings list
│   │   └── booking_detail.php # Booking management
│   ├── admin/
│   │   ├── users.php         # User management
│   │   └── garages.php       # Garage management
│   └── assets/
│       ├── css/
│       │   └── styles.css    # Main stylesheet
│       └── js/
│           └── main.js       # JavaScript for interactivity
└── database/
    └── schema.sql            # Database schema and sample data
```

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 8+ (Procedural)
- **Database**: MySQL (compatible with cloud MySQL services)
- **Icons**: Font Awesome (CDN)
- **Fonts**: Google Fonts - Inter

## 📋 Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) or PHP built-in server
- PHP extensions: PDO, pdo_mysql

## 🔧 Installation & Setup

### Step 1: Clone/Download the Project

Download or clone this repository to your web server directory:
- **XAMPP/WAMP**: `C:\xampp\htdocs\final_project\` or `C:\wamp64\www\final_project\`
- **Linux/Mac**: `/var/www/html/final_project/` or your preferred location

### Step 2: Configure Database

#### Option A: Localhost (XAMPP/WAMP)

1. Open `config/db.php` and update the database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty for XAMPP/WAMP
define('DB_NAME', 'railway');
```

#### Option B: Cloud MySQL (ClearDB, PlanetScale, AWS RDS)

1. Get your cloud MySQL connection details from your provider
2. Open `config/db.php` and update:
```php
define('DB_HOST', 'your-cloud-host.com');  // e.g., us-cdbr-east-05.cleardb.net
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');
define('DB_NAME', 'your-database-name');
```

### Step 3: Create Database

#### Using phpMyAdmin (XAMPP/WAMP):
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "New" to create a database
3. Name it `car_service_portal`
4. Click "Import" tab
5. Select `database/schema.sql` file
6. Click "Go" to import

#### Using MySQL Command Line:
```bash
mysql -u root -p < database/schema.sql
```

#### Using Cloud MySQL:
1. Connect to your cloud MySQL database
2. Run the SQL commands from `database/schema.sql`
3. Or use your provider's import tool

### Step 4: Configure Web Server

#### Option A: Using XAMPP/WAMP
1. Ensure Apache and MySQL are running
2. Access the site at: `http://localhost/final_project/public/index.php`

#### Option B: Using PHP Built-in Server
```bash
cd final_project
php -S localhost:8000 -t public
```
Access at: `http://localhost:8000/index.php`

#### Option C: Production Server
1. Point your web server document root to the `public` directory
2. Or configure virtual host to serve from `public` folder
3. Update all paths in the code if needed (currently uses absolute paths starting with `/public/`)

### Step 5: Test the Application

1. Open your browser and navigate to the application URL
2. You should see the home page

## 👤 Demo Accounts

The database includes sample accounts for testing:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@carservice.com | password123 |
| Garage Owner | garage1@example.com | password123 |
| Garage Owner | garage2@example.com | password123 |
| Customer | customer1@example.com | password123 |
| Customer | customer2@example.com | password123 |

**⚠️ Important**: Change these passwords in production!

## 📝 Key Features Implementation

### Security Features
- ✅ Password hashing with `password_hash()` and `password_verify()`
- ✅ Prepared statements (PDO) for all SQL queries
- ✅ Input validation and sanitization
- ✅ Session-based authentication
- ✅ Session regeneration on login
- ✅ Role-based access control
- ✅ CSRF protection (can be enhanced)

### Database Design
- ✅ Normalized database schema
- ✅ Foreign key constraints
- ✅ Appropriate indexes
- ✅ Sample data for testing

### User Interface
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Semantic HTML5
- ✅ ARIA attributes for accessibility
- ✅ Client-side and server-side validation
- ✅ User-friendly error messages
- ✅ Modern, professional design

### SEO & Accessibility
- ✅ Semantic HTML tags
- ✅ Meta descriptions
- ✅ Proper heading hierarchy
- ✅ Alt text for images (when added)
- ✅ Keyboard navigation support
- ✅ ARIA labels

## 🔐 Security Notes

1. **Change Default Passwords**: Update all default passwords in production
2. **Database Credentials**: Never commit `config/db.php` with real credentials to version control
3. **Error Display**: In production, disable detailed error messages in PHP
4. **HTTPS**: Use HTTPS in production for secure data transmission
5. **Session Security**: Consider adding session timeout and secure cookie flags

## 🧪 Testing Checklist

- [ ] User registration works
- [ ] Login works for all roles
- [ ] Customers can browse garages
- [ ] Customers can book services
- [ ] Garage owners can manage services
- [ ] Garage owners can update booking status
- [ ] Admins can manage users and garages
- [ ] Forms validate correctly
- [ ] Responsive design works on mobile
- [ ] All links and navigation work


### ✅ User Authentication
- Registration, login, logout
- Three roles: customer, garage_owner, admin
- Password hashing with `password_hash()`
- Session management
- Role-based access control

### ✅ Database Setup
- Complete MySQL schema
- 5 main tables: users, garages, services, vehicles, bookings
- Foreign keys and indexes
- Sample data

### ✅ CRUD Operations
- Services: Create, Read, Update (garage owners)
- Bookings: Create, Read, Update, Delete (customers)
- Vehicles: Create, Read (customers)
- Users/Garages: Read, Update (admins)

### ✅ Dynamic Web Design
- Responsive layout (CSS Grid/Flexbox)
- Home page with hero section
- Garage listing with filters
- Garage details with services
- Role-aware dashboards
- JavaScript for validation and interactivity

### ✅ Web Standards & Accessibility
- Semantic HTML
- ARIA attributes
- Good color contrast
- Keyboard navigation
- Form labels and placeholders

### ✅ SEO Techniques
- Page titles and meta descriptions
- Proper heading hierarchy
- Clean URLs
- Internal linking

### ✅ Privacy & Security
- Password hashing
- Prepared statements
- Input validation
- Session security
- Privacy policy page


## 🐛 Troubleshooting

### Database Connection Error
- Check database credentials in `config/db.php`
- Ensure MySQL is running
- Verify database exists

### Page Not Found (404)
- Check file paths (currently uses absolute paths starting with `/public/`)
- Verify web server configuration
- Ensure `.htaccess` is configured if using Apache

### Session Issues
- Check PHP session configuration
- Ensure `session_start()` is called
- Verify write permissions for session directory

### CSS/JS Not Loading
- Check file paths in `includes/header.php`
- Verify files exist in `public/assets/`
- Check browser console for errors

## 📄 License

This project is created for educational purposes as requirement for final Assessment (ICT726 Assignment 4).

## 👨‍💻 Development Notes

- All SQL queries use prepared statements for security
- Error handling is implemented but can be enhanced
- The code follows procedural PHP style (no frameworks)
- Paths use absolute paths starting with `/public/` - adjust if needed for your setup

## 📞 Support

**Built with ❤️ for ICT726 Assignment 4**
