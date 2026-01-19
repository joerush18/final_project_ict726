<?php
/**
 * Booking Detail Page
 * Shows detailed information about a specific booking
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$bookingId = $_GET['id'] ?? 0;
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

$pdo = getDBConnection();

// Get booking details
$query = "SELECT b.*, g.name as garage_name, g.address as garage_address, g.phone as garage_phone,
          s.name as service_name, s.description as service_description, s.price, s.duration_minutes,
          v.make, v.model, v.year, v.license_plate, v.color,
          u.name as customer_name, u.email as customer_email
          FROM bookings b
          JOIN garages g ON b.garage_id = g.id
          JOIN services s ON b.service_id = s.id
          JOIN vehicles v ON b.vehicle_id = v.id
          JOIN users u ON b.user_id = u.id
          WHERE b.id = ?";

$params = [$bookingId];

// Restrict access based on role
if ($userRole === 'customer') {
    $query .= " AND b.user_id = ?";
    $params[] = $userId;
} elseif ($userRole === 'garage_owner') {
    $query .= " AND g.owner_user_id = ?";
    $params[] = $userId;
}
// Admin can see all bookings

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: /dashboard.php');
    exit();
}

$pageTitle = 'Booking Details - Car Service Portal';
$pageDescription = 'View details for booking #' . $bookingId;
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-5xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Booking Details</h1>
            <p class="page-subtitle text-sm text-slate-500">Booking #<?php echo $bookingId; ?></p>
        </div>

        <div class="booking-detail">
            <div class="detail-card rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="detail-header flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($booking['service_name']); ?></h2>
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>

                <div class="detail-section mt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Service Information</h3>
                    <div class="detail-grid mt-3 grid gap-4 md:grid-cols-3">
                        <div class="detail-item">
                            <strong>Service:</strong>
                            <p><?php echo htmlspecialchars($booking['service_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Price:</strong>
                            <p>$<?php echo number_format($booking['price'], 2); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Duration:</strong>
                            <p><?php echo $booking['duration_minutes']; ?> minutes</p>
                        </div>
                        <?php if ($booking['service_description']): ?>
                            <div class="detail-item full-width md:col-span-3">
                                <strong>Description:</strong>
                                <p><?php echo htmlspecialchars($booking['service_description']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-section mt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Booking Schedule</h3>
                    <div class="detail-grid mt-3 grid gap-4 md:grid-cols-3">
                        <div class="detail-item">
                            <strong>Date:</strong>
                            <p><?php echo date('l, F j, Y', strtotime($booking['booking_date'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Time:</strong>
                            <p><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Booked On:</strong>
                            <p><?php echo date('F j, Y g:i A', strtotime($booking['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="detail-section mt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Vehicle Information</h3>
                    <div class="detail-grid mt-3 grid gap-4 md:grid-cols-3">
                        <div class="detail-item">
                            <strong>Make & Model:</strong>
                            <p><?php echo htmlspecialchars($booking['make'] . ' ' . $booking['model']); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Year:</strong>
                            <p><?php echo htmlspecialchars($booking['year']); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>License Plate:</strong>
                            <p><?php echo htmlspecialchars($booking['license_plate']); ?></p>
                        </div>
                        <?php if ($booking['color']): ?>
                            <div class="detail-item">
                                <strong>Color:</strong>
                                <p><?php echo htmlspecialchars($booking['color']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-section mt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Garage Information</h3>
                    <div class="detail-grid mt-3 grid gap-4 md:grid-cols-3">
                        <div class="detail-item">
                            <strong>Garage Name:</strong>
                            <p><?php echo htmlspecialchars($booking['garage_name']); ?></p>
                        </div>
                        <div class="detail-item full-width md:col-span-3">
                            <strong>Address:</strong>
                            <p><?php echo htmlspecialchars($booking['garage_address']); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Phone:</strong>
                            <p><a href="tel:<?php echo htmlspecialchars($booking['garage_phone']); ?>"><?php echo htmlspecialchars($booking['garage_phone']); ?></a></p>
                        </div>
                    </div>
                </div>

                <?php if ($userRole === 'customer'): ?>
                    <div class="detail-section mt-6">
                        <h3 class="text-lg font-semibold text-slate-900">Customer Information</h3>
                        <div class="detail-grid mt-3 grid gap-4 md:grid-cols-2">
                            <div class="detail-item">
                                <strong>Name:</strong>
                                <p><?php echo htmlspecialchars($booking['customer_name']); ?></p>
                            </div>
                            <div class="detail-item">
                                <strong>Email:</strong>
                                <p><?php echo htmlspecialchars($booking['customer_email']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($booking['notes']): ?>
                    <div class="detail-section mt-6">
                        <h3 class="text-lg font-semibold text-slate-900">Notes</h3>
                        <p class="mt-2 text-sm text-slate-600"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="detail-actions mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-4">
                    <?php if ($userRole === 'customer' && ($booking['status'] === 'pending' || $booking['status'] === 'approved')): ?>
                        <a href="/cancel_booking.php?id=<?php echo $bookingId; ?>" 
                           class="btn btn-danger rounded-xl bg-rose-600 px-5 py-2.5 text-white hover:bg-rose-700"
                           onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel Booking</a>
                    <?php endif; ?>
                    <a href="/dashboard.php" class="btn btn-secondary rounded-xl bg-slate-900 px-5 py-2.5 text-white hover:bg-slate-800">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
