<?php
/**
 * Garage Owner Bookings Management
 * View and manage bookings for their garage
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('garage_owner');

$userId = getCurrentUserId();
$pdo = getDBConnection();

// Get garage owned by this user
$stmt = $pdo->prepare("SELECT id, name FROM garages WHERE owner_user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$garage = $stmt->fetch();

if (!$garage) {
    header('Location: /dashboard.php');
    exit();
}

$garageId = $garage['id'];

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Build query
$query = "SELECT b.*, u.name as customer_name, u.email as customer_email,
          s.name as service_name, s.price,
          v.make, v.model, v.license_plate
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN services s ON b.service_id = s.id
          JOIN vehicles v ON b.vehicle_id = v.id
          WHERE b.garage_id = ?";

$params = [$garageId];

if ($statusFilter !== 'all') {
    $query .= " AND b.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'Manage Bookings - Car Service Portal';
$pageDescription = 'View and manage bookings for ' . htmlspecialchars($garage['name']);
include __DIR__ . '/../../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Manage Bookings</h1>
            <p class="page-subtitle text-sm text-slate-500">Garage: <?php echo htmlspecialchars($garage['name']); ?></p>
        </div>

        <!-- Status Filter -->
        <div class="filters-section rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="status-filters flex flex-wrap gap-2">
                <a href="/garage_owner/bookings.php?status=all" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'all' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">All</a>
                <a href="/garage_owner/bookings.php?status=pending" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'pending' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Pending</a>
                <a href="/garage_owner/bookings.php?status=approved" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'approved' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Approved</a>
                <a href="/garage_owner/bookings.php?status=rejected" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'rejected' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Rejected</a>
                <a href="/garage_owner/bookings.php?status=completed" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'completed' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Completed</a>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="bookings-section">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No bookings found<?php echo $statusFilter !== 'all' ? ' with status "' . $statusFilter . '"' : ''; ?>.</p>
                </div>
            <?php else: ?>
                <div class="bookings-list mt-6 grid gap-4 md:grid-cols-2">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="booking-header flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="booking-body mt-3 text-sm text-slate-600">
                                <div class="booking-info-grid grid gap-4 md:grid-cols-3">
                                    <div>
                                        <p><strong>Customer:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['customer_email']); ?></p>
                                    </div>
                                    <div>
                                        <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['license_plate'] . ')'); ?></p>
                                        <p><strong>Price:</strong> $<?php echo number_format($booking['price'], 2); ?></p>
                                    </div>
                                    <div>
                                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                                    </div>
                                </div>
                                <?php if ($booking['notes']): ?>
                                    <div class="booking-notes mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                                        <strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="booking-footer mt-4 flex justify-end">
                                <a href="/garage_owner/booking_detail.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary rounded-xl bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">View & Manage</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
