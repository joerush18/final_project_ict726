<?php
/**
 * User Registration Page
 * Allows new users to register as customers
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error. Please try again.';
        }
    }
    
    // Register user if no errors
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $password_hash]);
            
            $success = true;
            // Auto-login after registration
            $userId = $pdo->lastInsertId();
            loginUser($userId, 'customer', $name);
            
            // Redirect to dashboard after 2 seconds
            header('Refresh: 2; url=/dashboard.php');
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register - Car Service Portal';
$pageDescription = 'Create a new account to book car services online';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="auth-container flex justify-center">
            <div class="auth-card w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl">
                <h1 class="page-title text-2xl font-semibold text-slate-900">Create Account</h1>
                <p class="auth-subtitle text-sm text-slate-500">Register to start booking car services</p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        Registration successful! Redirecting to dashboard...
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="auth-form mt-6 space-y-4" novalidate>
                        <div class="form-group">
                            <label for="name">Full Name <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                required 
                                autocomplete="name"
                                aria-required="true"
                                aria-describedby="name-error"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                            <span id="name-error" class="error-message" role="alert"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required 
                                autocomplete="email"
                                aria-required="true"
                                aria-describedby="email-error"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                            <span id="email-error" class="error-message" role="alert"></span>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                minlength="8"
                                autocomplete="new-password"
                                aria-required="true"
                                aria-describedby="password-error"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                            <span id="password-error" class="error-message" role="alert"></span>
                            <small class="form-hint">Must be at least 8 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required 
                                autocomplete="new-password"
                                aria-required="true"
                                aria-describedby="confirm-password-error"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                            <span id="confirm-password-error" class="error-message" role="alert"></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block w-full rounded-xl bg-blue-600 py-2.5 text-white hover:bg-blue-700">Register</button>
                    </form>
                    
                    <p class="auth-footer mt-4 text-sm text-slate-500">
                        Already have an account? <a href="/login.php" class="font-medium text-blue-600 hover:text-blue-700">Login here</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
