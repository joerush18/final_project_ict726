<?php
/**
 * Database Configuration
 * 
 * For localhost (XAMPP/WAMP):
 * - host: 'localhost'
 * - username: 'root'
 * - password: '' (empty)
 * - dbname: 'car_service_portal'
 * 
 * For cloud MySQL (ClearDB, PlanetScale, AWS RDS):
 * - Update these values with your cloud provider's credentials
 */

// Database configuration
// Use environment variables in production (recommended)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'car_service_portal');

/**
 * Create database connection using PDO
 * Uses prepared statements for security
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // In production, log this error instead of displaying it
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
