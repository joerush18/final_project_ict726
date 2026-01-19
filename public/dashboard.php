<?php
/**
 * User Dashboard
 * Role-aware dashboard showing different content based on user role
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$pdo = getDBConnection();
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

$pageTitle = 'Dashboard - Car Service Portal';
$pageDescription = 'Manage your bookings, services, and account';

// Get user-specific data based on role
if ($userRole === 'customer') {
    // Customer dashboard data
    $stmt = $pdo->prepare("SELECT b.*, g.name as garage_name, s.name as service_name, s.price, 
                           v.make, v.model, v.license_plate
                           FROM bookings b
                           JOIN garages g ON b.garage_id = g.id
                           JOIN services s ON b.service_id = s.id
                           JOIN vehicles v ON b.vehicle_id = v.id
                           WHERE b.user_id = ?
                           ORDER BY b.booking_date DESC, b.booking_time DESC
                           LIMIT 10");
    $stmt->execute([$userId]);
    $bookings = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM vehicles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $vehicleCount = $stmt->fetch()['count'];
    
} elseif ($userRole === 'garage_owner') {
    // Garage owner dashboard data
    // Get garage owned by this user
    $stmt = $pdo->prepare("SELECT id FROM garages WHERE owner_user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $garage = $stmt->fetch();
    
    if ($garage) {
        $garageId = $garage['id'];
        
        $stmt = $pdo->prepare("SELECT b.*, u.name as customer_name, s.name as service_name, s.price,
                               v.make, v.model, v.license_plate
                               FROM bookings b
                               JOIN users u ON b.user_id = u.id
                               JOIN services s ON b.service_id = s.id
                               JOIN vehicles v ON b.vehicle_id = v.id
                               WHERE b.garage_id = ?
                               ORDER BY b.booking_date DESC, b.booking_time DESC
                               LIMIT 10");
        $stmt->execute([$garageId]);
        $bookings = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM services WHERE garage_id = ? AND status = 'active'");
        $stmt->execute([$garageId]);
        $serviceCount = $stmt->fetch()['count'];
    } else {
        $bookings = [];
        $serviceCount = 0;
    }
    
} else {
    // Admin dashboard data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'");
    $customerCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM garages WHERE status = 'active'");
    $garageCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    $pendingBookings = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT b.*, u.name as customer_name, g.name as garage_name, s.name as service_name,
                         v.make, v.model, v.license_plate
                         FROM bookings b
                         JOIN users u ON b.user_id = u.id
                         JOIN garages g ON b.garage_id = g.id
                         JOIN services s ON b.service_id = s.id
                         JOIN vehicles v ON b.vehicle_id = v.id
                         ORDER BY b.created_at DESC
                         LIMIT 10");
    $bookings = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="dashboard-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Dashboard</h1>
            <p class="page-subtitle text-sm text-slate-500">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>

        <?php if ($userRole === 'customer'): ?>
            <!-- Customer Dashboard -->
            <div class="dashboard-stats grid gap-4 md:grid-cols-2">
                <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="stat-content">
                        <h3><?php echo count($bookings); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="stat-icon"><i class="fas fa-car"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $vehicleCount; ?></h3>
                        <p>Registered Vehicles</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="section-header flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Recent Bookings</h2>
                    <a href="/bookings.php" class="btn btn-secondary rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">View All</a>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No bookings yet. <a href="/garages.php">Browse garages</a> to make your first booking!</p>
                    </div>
                <?php else: ?>
                    <div class="bookings-list mt-4 grid gap-4 md:grid-cols-2">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="booking-header flex items-center justify-between border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                <div class="booking-body mt-3 text-sm text-slate-600">
                                    <p><strong>Garage:</strong> <?php echo htmlspecialchars($booking['garage_name']); ?></p>
                                    <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['license_plate'] . ')'); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                                    <p><strong>Price:</strong> $<?php echo number_format($booking['price'], 2); ?></p>
                                </div>
                                <div class="booking-footer mt-4 flex justify-end">
                                    <a href="/booking_detail.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary rounded-xl bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-actions mt-6 flex flex-wrap justify-center gap-3">
                <a href="/garages.php" class="btn btn-primary rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Book a Service</a>
                <a href="/vehicles.php" class="btn btn-secondary rounded-xl bg-slate-900 px-5 py-2.5 text-white hover:bg-slate-800">Manage Vehicles</a>
            </div>

        <?php elseif ($userRole === 'garage_owner'): ?>
            <!-- Garage Owner Dashboard -->
            <?php if (!$garage): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    You don't have a garage registered yet. Please contact admin to set up your garage.
                </div>
            <?php else: ?>
                <div class="dashboard-stats grid gap-4 md:grid-cols-2">
                    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                        <div class="stat-content">
                            <h3><?php echo count($bookings); ?></h3>
                            <p>Recent Bookings</p>
                        </div>
                    </div>
                    <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="stat-icon"><i class="fas fa-wrench"></i></div>
                        <div class="stat-content">
                            <h3><?php echo $serviceCount; ?></h3>
                            <p>Active Services</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-section rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="section-header flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-slate-900">Recent Bookings</h2>
                        <a href="/garage_owner/bookings.php" class="btn btn-secondary rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">Manage All</a>
                    </div>
                    
                    <?php if (empty($bookings)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>No bookings yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="bookings-list mt-4 grid gap-4 md:grid-cols-2">
                            <?php foreach ($bookings as $booking): ?>
                                <div class="booking-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <div class="booking-header flex items-center justify-between border-b border-slate-100 pb-3">
                                        <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                                        <span class="status-badge status-<?php echo $booking['status']; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                    <div class="booking-body mt-3 text-sm text-slate-600">
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                        <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['license_plate'] . ')'); ?></p>
                                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                                        <p><strong>Price:</strong> $<?php echo number_format($booking['price'], 2); ?></p>
                                    </div>
                                    <div class="booking-footer mt-4 flex justify-end">
                                        <a href="/garage_owner/booking_detail.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary rounded-xl bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">Manage</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dashboard-actions mt-6 flex justify-center">
                    <a href="/garage_owner/services.php" class="btn btn-primary rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Manage Services</a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="dashboard-stats grid gap-4 md:grid-cols-3">
                <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $customerCount; ?></h3>
                        <p>Total Customers</p>
                    </div>
                </div>
                <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="stat-icon"><i class="fas fa-building"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $garageCount; ?></h3>
                        <p>Active Garages</p>
                    </div>
                </div>
                <div class="stat-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $pendingBookings; ?></h3>
                        <p>Pending Bookings</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="section-header flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Recent Bookings</h2>
                    <a href="/admin/bookings.php" class="btn btn-secondary rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">View All</a>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No bookings in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="bookings-list mt-4 grid gap-4 md:grid-cols-2">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="booking-header flex items-center justify-between border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                <div class="booking-body mt-3 text-sm text-slate-600">
                                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                    <p><strong>Garage:</strong> <?php echo htmlspecialchars($booking['garage_name']); ?></p>
                                    <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['license_plate'] . ')'); ?></p>
                                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-actions mt-6 flex flex-wrap justify-center gap-3">
                <a href="/admin/users.php" class="btn btn-primary rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Manage Users</a>
                <a href="/admin/garages.php" class="btn btn-secondary rounded-xl bg-slate-900 px-5 py-2.5 text-white hover:bg-slate-800">Manage Garages</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
