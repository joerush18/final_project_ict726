<?php
/**
 * User Login Page
 * Handles authentication for all user roles
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

$errors = [];
$redirect = $_GET['redirect'] ?? '/dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // Authenticate user
    if (empty($errors)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if user is active
                if ($user['status'] === 'inactive') {
                    $errors[] = 'Your account has been deactivated. Please contact support.';
                } else {
                    // Login successful
                    loginUser($user['id'], $user['role'], $user['name']);
                    
                    // Redirect to original destination or dashboard
                    $redirect = $_POST['redirect'] ?? '/dashboard.php';
                    header('Location: ' . $redirect);
                    exit();
                }
            } else {
                $errors[] = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $errors[] = 'Login failed. Please try again.';
        }
    }
}

$pageTitle = 'Login - Car Service Portal';
$pageDescription = 'Login to your account to manage bookings and services';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="auth-container flex justify-center">
            <div class="auth-card w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-xl">
                <h1 class="page-title text-2xl font-semibold text-slate-900">Login</h1>
                <p class="auth-subtitle text-sm text-slate-500">Sign in to your account</p>
                
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
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                    
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
                            autocomplete="current-password"
                            aria-required="true"
                            aria-describedby="password-error"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        >
                        <span id="password-error" class="error-message" role="alert"></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block w-full rounded-xl bg-blue-600 py-2.5 text-white hover:bg-blue-700">Login</button>
                </form>
                
                <p class="auth-footer mt-4 text-sm text-slate-500">
                    Don't have an account? <a href="/register.php" class="font-medium text-blue-600 hover:text-blue-700">Register here</a>
                </p>
                
                <div class="demo-accounts mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600">
                    <h3 class="mb-2 text-sm font-semibold text-slate-900">Demo Accounts (for testing)</h3>
                    <p><strong>Admin:</strong> admin@carservice.com / password123</p>
                    <p><strong>Garage Owner:</strong> garage1@example.com / password123</p>
                    <p><strong>Customer:</strong> customer1@example.com / password123</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
