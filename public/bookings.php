<?php
/**
 * Customer Bookings Page
 * Shows all bookings for the logged-in customer
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$userId = getCurrentUserId();
$pdo = getDBConnection();

// Get filter
$statusFilter = $_GET['status'] ?? 'all';

// Build query
$query = "SELECT b.*, g.name as garage_name, s.name as service_name, s.price,
          v.make, v.model, v.license_plate
          FROM bookings b
          JOIN garages g ON b.garage_id = g.id
          JOIN services s ON b.service_id = s.id
          JOIN vehicles v ON b.vehicle_id = v.id
          WHERE b.user_id = ?";

$params = [$userId];

if ($statusFilter !== 'all') {
    $query .= " AND b.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$pageTitle = 'My Bookings - Car Service Portal';
$pageDescription = 'View and manage your car service bookings';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">My Bookings</h1>
            <p class="page-subtitle text-sm text-slate-500">View and manage your service bookings</p>
        </div>

        <!-- Status Filter -->
        <div class="filters-section rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="status-filters flex flex-wrap gap-2">
                <a href="/bookings.php?status=all" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'all' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">All</a>
                <a href="/bookings.php?status=pending" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'pending' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Pending</a>
                <a href="/bookings.php?status=approved" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'approved' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Approved</a>
                <a href="/bookings.php?status=completed" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'completed' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Completed</a>
                <a href="/bookings.php?status=cancelled" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $statusFilter === 'cancelled' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Cancelled</a>
            </div>
        </div>

        <!-- Bookings List -->
        <div class="bookings-section">
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No bookings found<?php echo $statusFilter !== 'all' ? ' with status "' . $statusFilter . '"' : ''; ?>.</p>
                    <a href="/garages.php" class="btn btn-primary">Browse Garages</a>
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
                                        <p><strong>Garage:</strong> <?php echo htmlspecialchars($booking['garage_name']); ?></p>
                                        <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model'] . ' (' . $booking['license_plate'] . ')'); ?></p>
                                    </div>
                                    <div>
                                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></p>
                                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                                    </div>
                                    <div>
                                        <p><strong>Price:</strong> $<?php echo number_format($booking['price'], 2); ?></p>
                                        <p><strong>Booked:</strong> <?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?></p>
                                    </div>
                                </div>
                                <?php if ($booking['notes']): ?>
                                    <div class="booking-notes mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                                        <strong>Notes:</strong> <?php echo htmlspecialchars($booking['notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="booking-footer mt-4 flex flex-wrap justify-end gap-2">
                                <a href="/booking_detail.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary rounded-xl bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">View Details</a>
                                <?php if ($booking['status'] === 'pending' || $booking['status'] === 'approved'): ?>
                                    <a href="/cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-danger rounded-xl bg-rose-600 px-3 py-2 text-white hover:bg-rose-700"
                                       onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
