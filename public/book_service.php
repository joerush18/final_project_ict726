<?php
/**
 * Book Service Page
 * Allows customers to book a service
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$garageId = $_GET['garage_id'] ?? 0;
$serviceId = $_GET['service_id'] ?? 0;
$userId = getCurrentUserId();

$pdo = getDBConnection();
$errors = [];
$success = false;

// Get service and garage details
if ($serviceId && $garageId) {
    $stmt = $pdo->prepare("SELECT s.*, g.name as garage_name 
                           FROM services s 
                           JOIN garages g ON s.garage_id = g.id 
                           WHERE s.id = ? AND s.garage_id = ? AND s.status = 'active' AND g.status = 'active'");
    $stmt->execute([$serviceId, $garageId]);
    $service = $stmt->fetch();
    
    if (!$service) {
        header('Location: /garages.php');
        exit();
    }
} else {
    header('Location: /garages.php');
    exit();
}

// Get user's vehicles
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$vehicles = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = $_POST['vehicle_id'] ?? 0;
    $bookingDate = $_POST['booking_date'] ?? '';
    $bookingTime = $_POST['booking_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    
    // Validation
    if (empty($vehicleId) || $vehicleId == 0) {
        $errors[] = 'Please select a vehicle';
    } else {
        // Verify vehicle belongs to user
        $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE id = ? AND user_id = ?");
        $stmt->execute([$vehicleId, $userId]);
        if (!$stmt->fetch()) {
            $errors[] = 'Invalid vehicle selected';
        }
    }
    
    if (empty($bookingDate)) {
        $errors[] = 'Booking date is required';
    } else {
        $dateObj = DateTime::createFromFormat('Y-m-d', $bookingDate);
        if (!$dateObj || $dateObj->format('Y-m-d') !== $bookingDate) {
            $errors[] = 'Invalid date format';
        } elseif ($bookingDate < date('Y-m-d')) {
            $errors[] = 'Booking date cannot be in the past';
        }
    }
    
    if (empty($bookingTime)) {
        $errors[] = 'Booking time is required';
    }
    
    // Check for conflicting bookings (same garage, same date/time)
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM bookings 
                               WHERE garage_id = ? AND booking_date = ? AND booking_time = ? 
                               AND status IN ('pending', 'approved')");
        $stmt->execute([$garageId, $bookingDate, $bookingTime]);
        if ($stmt->fetch()) {
            $errors[] = 'This time slot is already booked. Please choose another time.';
        }
    }
    
    // Create booking if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, garage_id, service_id, vehicle_id, booking_date, booking_time, status, notes) 
                                   VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
            $stmt->execute([$userId, $garageId, $serviceId, $vehicleId, $bookingDate, $bookingTime, $notes]);
            
            $success = true;
            $bookingId = $pdo->lastInsertId();
        } catch (PDOException $e) {
            $errors[] = 'Booking failed. Please try again.';
        }
    }
}

$pageTitle = 'Book Service - ' . htmlspecialchars($service['name']);
$pageDescription = 'Book ' . htmlspecialchars($service['name']) . ' at ' . htmlspecialchars($service['garage_name']);
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Book Service</h1>
            <p class="page-subtitle text-sm text-slate-500">Complete your booking details</p>
        </div>

        <div class="booking-container grid gap-6 lg:grid-cols-3">
            <div class="booking-summary lg:col-span-1">
                <h2 class="text-xl font-semibold text-slate-900">Service Details</h2>
                <div class="summary-card mt-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><strong>Garage:</strong> <?php echo htmlspecialchars($service['garage_name']); ?></p>
                    <p><strong>Price:</strong> $<?php echo number_format($service['price'], 2); ?></p>
                    <p><strong>Duration:</strong> <?php echo $service['duration_minutes']; ?> minutes</p>
                    <?php if ($service['description']): ?>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($service['description']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="booking-form-section lg:col-span-2">
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        Booking created successfully! 
                        <a href="/booking_detail.php?id=<?php echo $bookingId; ?>">View booking details</a>
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

                    <?php if (empty($vehicles)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            You need to add a vehicle first. <a href="/vehicles.php?action=add">Add Vehicle</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" class="booking-form rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" novalidate>
                            <div class="form-group">
                                <label for="vehicle_id">Select Vehicle <span class="required">*</span></label>
                                <select id="vehicle_id" name="vehicle_id" required aria-required="true"
                                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="">Choose a vehicle...</option>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                        <option value="<?php echo $vehicle['id']; ?>" 
                                                <?php echo (isset($_POST['vehicle_id']) && $_POST['vehicle_id'] == $vehicle['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ') - ' . $vehicle['license_plate']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="booking_date">Booking Date <span class="required">*</span></label>
                                <input 
                                    type="date" 
                                    id="booking_date" 
                                    name="booking_date" 
                                    value="<?php echo htmlspecialchars($_POST['booking_date'] ?? ''); ?>"
                                    min="<?php echo date('Y-m-d'); ?>"
                                    required
                                    aria-required="true"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>

                            <div class="form-group">
                                <label for="booking_time">Booking Time <span class="required">*</span></label>
                                <input 
                                    type="time" 
                                    id="booking_time" 
                                    name="booking_time" 
                                    value="<?php echo htmlspecialchars($_POST['booking_time'] ?? ''); ?>"
                                    required
                                    aria-required="true"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                                <small class="form-hint">Garage hours: 9:00 AM - 6:00 PM</small>
                            </div>

                            <div class="form-group">
                                <label for="notes">Additional Notes (Optional)</label>
                                <textarea 
                                    id="notes" 
                                    name="notes" 
                                    rows="4" 
                                    placeholder="Any special instructions or notes for the garage..."
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                ><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-actions mt-6 flex gap-3">
                                <button type="submit" class="btn btn-primary rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Confirm Booking</button>
                                <a href="/garage_detail.php?id=<?php echo $garageId; ?>" class="btn btn-secondary rounded-xl bg-slate-900 px-5 py-2.5 text-white hover:bg-slate-800">Cancel</a>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
