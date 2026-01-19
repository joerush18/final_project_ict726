<?php
// Ensure auth functions are available
require_once __DIR__ . '/auth.php';
?>
<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="container">
        <div class="navbar-brand">
            <a href="/index.php" class="navbar-logo">
                <i class="fas fa-car"></i>
                <span>Car Service Portal</span>
            </a>
            <button class="navbar-toggle" aria-label="Toggle navigation" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        <ul class="navbar-menu">
            <li><a href="/index.php">Home</a></li>
            <li><a href="/garages.php">Browse Garages</a></li>
            
            <?php if (isLoggedIn()): ?>
                <li><a href="/dashboard.php">Dashboard</a></li>
                
                <?php if (isGarageOwner() || isAdmin()): ?>
                    <li><a href="/garage_owner/services.php">Manage Services</a></li>
                <?php endif; ?>
                
                <?php if (isAdmin()): ?>
                    <li><a href="/admin/users.php">Manage Users</a></li>
                    <li><a href="/admin/garages.php">Manage Garages</a></li>
                <?php endif; ?>
                
                <li class="navbar-user">
                    <span class="user-name">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    </span>
                    <a href="/logout.php" class="btn-logout">Logout</a>
                </li>
            <?php else: ?>
                <li><a href="/login.php">Login</a></li>
                <li><a href="/register.php" class="btn btn-primary navbar-cta">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
