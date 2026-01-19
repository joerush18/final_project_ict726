<?php
/**
 * Cancel Booking Action
 * Allows customers to cancel their bookings
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$bookingId = $_GET['id'] ?? 0;
$userId = getCurrentUserId();

if (!$bookingId) {
    header('Location: /bookings.php');
    exit();
}

$pdo = getDBConnection();

// Verify booking belongs to user and can be cancelled
$stmt = $pdo->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: /bookings.php');
    exit();
}

// Only allow cancellation of pending or approved bookings
if ($booking['status'] !== 'pending' && $booking['status'] !== 'approved') {
    header('Location: /booking_detail.php?id=' . $bookingId);
    exit();
}

// Cancel the booking
try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $userId]);
    
    header('Location: /booking_detail.php?id=' . $bookingId . '&cancelled=1');
    exit();
} catch (PDOException $e) {
    header('Location: /booking_detail.php?id=' . $bookingId . '&error=1');
    exit();
}
?>
