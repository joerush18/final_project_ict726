<?php
/**
 * Authentication Helper Functions
 * Handles user authentication, session management, and role-based access control
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user has a specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['user_role'] === $role;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if user is garage owner
 */
function isGarageOwner() {
    return hasRole('garage_owner');
}

/**
 * Check if user is customer
 */
function isCustomer() {
    return hasRole('customer');
}

/**
 * Require user to be logged in, redirect to login if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

/**
 * Require specific role, redirect to dashboard if not authorized
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /dashboard.php');
        exit();
    }
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Login user and set session
 */
function loginUser($userId, $userRole, $userName) {
    // Regenerate session ID to prevent session fixation attacks
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $userRole;
    $_SESSION['user_name'] = $userName;
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Redirect to role-appropriate dashboard
 */
function redirectToDashboard() {
    $role = getCurrentUserRole();
    
    switch ($role) {
        case 'admin':
            header('Location: /dashboard.php');
            break;
        case 'garage_owner':
            header('Location: /dashboard.php');
            break;
        case 'customer':
            header('Location: /dashboard.php');
            break;
        default:
            header('Location: /index.php');
    }
    exit();
}
?>
