<?php
/**
 * Garage Owner Booking Detail
 * View and update booking status
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('garage_owner');

$bookingId = $_GET['id'] ?? 0;
$userId = getCurrentUserId();
$pdo = getDBConnection();
$errors = [];
$success = false;

// Get garage owned by this user
$stmt = $pdo->prepare("SELECT id FROM garages WHERE owner_user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$garage = $stmt->fetch();

if (!$garage) {
    header('Location: /dashboard.php');
    exit();
}

$garageId = $garage['id'];

// Get booking details
$stmt = $pdo->prepare("SELECT b.*, u.name as customer_name, u.email as customer_email,
          g.name as garage_name,
          s.name as service_name, s.price, s.duration_minutes,
          v.make, v.model, v.year, v.license_plate, v.color
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN garages g ON b.garage_id = g.id
          JOIN services s ON b.service_id = s.id
          JOIN vehicles v ON b.vehicle_id = v.id
          WHERE b.id = ? AND b.garage_id = ?");
$stmt->execute([$bookingId, $garageId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: /garage_owner/bookings.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';
    
    $allowedStatuses = ['pending', 'approved', 'rejected', 'completed'];
    if (in_array($newStatus, $allowedStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ? AND garage_id = ?");
            $stmt->execute([$newStatus, $bookingId, $garageId]);
            $success = true;
            $booking['status'] = $newStatus; // Update local variable
        } catch (PDOException $e) {
            $errors[] = 'Failed to update booking status.';
        }
    } else {
        $errors[] = 'Invalid status selected.';
    }
}

$pageTitle = 'Booking Details - Car Service Portal';
$pageDescription = 'Manage booking #' . $bookingId;
include __DIR__ . '/../../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-5xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Booking Details</h1>
            <p class="page-subtitle text-sm text-slate-500">Booking #<?php echo $bookingId; ?></p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                Booking status updated successfully!
            </div>
        <?php endif; ?>

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

        <div class="booking-detail">
            <div class="detail-card rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="detail-header flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($booking['service_name']); ?></h2>
                    <span class="status-badge status-<?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>

                <div class="detail-section mt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Customer Information</h3>
                    <div class="detail-grid mt-3 grid gap-4 md:grid-cols-2">
                        <div class="detail-item">
                            <strong>Name:</strong>
                            <p><?php echo htmlspecialchars($booking['customer_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Email:</strong>
                            <p><a href="mailto:<?php echo htmlspecialchars($booking['customer_email']); ?>"><?php echo htmlspecialchars($booking['customer_email']); ?></a></p>
                        </div>
                    </div>
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
                    </div>
                </div>

                <div class="detail-section mt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Booking Schedule</h3>
                    <div class="detail-grid mt-3 grid gap-4 md:grid-cols-2">
                        <div class="detail-item">
                            <strong>Date:</strong>
                            <p><?php echo date('l, F j, Y', strtotime($booking['booking_date'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Time:</strong>
                            <p><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></p>
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

                <?php if ($booking['notes']): ?>
                    <div class="detail-section mt-6">
                        <h3 class="text-lg font-semibold text-slate-900">Customer Notes</h3>
                        <p class="mt-2 text-sm text-slate-600"><?php echo nl2br(htmlspecialchars($booking['notes'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Status Update Form -->
                <div class="detail-section mt-6">
                    <h3 class="text-lg font-semibold text-slate-900">Update Booking Status</h3>
                    <form method="POST" action="" class="status-form mt-3 flex flex-wrap items-end gap-3">
                        <div class="form-group">
                            <label for="status">Status <span class="required">*</span></label>
                            <select id="status" name="status" required aria-required="true"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $booking['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $booking['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Update Status</button>
                    </form>
                </div>

                <div class="detail-actions mt-6 flex justify-end border-t border-slate-100 pt-4">
                    <a href="/garage_owner/bookings.php" class="btn btn-secondary rounded-xl bg-slate-900 px-5 py-2.5 text-white hover:bg-slate-800">Back to Bookings</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
