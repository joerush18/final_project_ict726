<?php
/**
 * Vehicle Management Page
 * Allows customers to add, view, and manage their vehicles
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('customer');

$userId = getCurrentUserId();
$pdo = getDBConnection();
$errors = [];
$success = false;
$action = $_GET['action'] ?? 'list';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = $_POST['year'] ?? '';
    $licensePlate = trim($_POST['license_plate'] ?? '');
    $color = trim($_POST['color'] ?? '');
    
    // Validation
    if (empty($make)) {
        $errors[] = 'Make is required';
    }
    
    if (empty($model)) {
        $errors[] = 'Model is required';
    }
    
    if (empty($year) || !is_numeric($year) || $year < 1900 || $year > date('Y') + 1) {
        $errors[] = 'Valid year is required';
    }
    
    if (empty($licensePlate)) {
        $errors[] = 'License plate is required';
    }
    
    // Check for duplicate license plate for this user
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE user_id = ? AND license_plate = ?");
        $stmt->execute([$userId, $licensePlate]);
        if ($stmt->fetch()) {
            $errors[] = 'This license plate is already registered to your account';
        }
    }
    
    // Insert vehicle
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO vehicles (user_id, make, model, year, license_plate, color) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $make, $model, $year, $licensePlate, $color]);
            $success = true;
            $action = 'list'; // Switch to list view after success
        } catch (PDOException $e) {
            $errors[] = 'Failed to add vehicle. Please try again.';
        }
    }
}

// Get user's vehicles
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$vehicles = $stmt->fetchAll();

$pageTitle = 'Manage Vehicles - Car Service Portal';
$pageDescription = 'Add and manage your registered vehicles';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Manage Vehicles</h1>
            <p class="page-subtitle text-sm text-slate-500">Add and manage your registered vehicles</p>
        </div>

        <?php if ($action === 'add'): ?>
            <!-- Add Vehicle Form -->
            <div class="form-container flex justify-center">
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        Vehicle added successfully! <a href="/vehicles.php">View all vehicles</a>
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

                    <form method="POST" action="" class="vehicle-form w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm" novalidate>
                        <div class="form-group">
                            <label for="make">Make <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="make" 
                                name="make" 
                                value="<?php echo htmlspecialchars($_POST['make'] ?? ''); ?>"
                                required
                                placeholder="e.g., Toyota, Honda, Ford"
                                aria-required="true"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                        </div>

                        <div class="form-group">
                            <label for="model">Model <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="model" 
                                name="model" 
                                value="<?php echo htmlspecialchars($_POST['model'] ?? ''); ?>"
                                required
                                placeholder="e.g., Camry, Civic, F-150"
                                aria-required="true"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                        </div>

                        <div class="form-group">
                            <label for="year">Year <span class="required">*</span></label>
                            <input 
                                type="number" 
                                id="year" 
                                name="year" 
                                value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>"
                                min="1900"
                                max="<?php echo date('Y') + 1; ?>"
                                required
                                aria-required="true"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                        </div>

                        <div class="form-group">
                            <label for="license_plate">License Plate <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="license_plate" 
                                name="license_plate" 
                                value="<?php echo htmlspecialchars($_POST['license_plate'] ?? ''); ?>"
                                required
                                placeholder="e.g., ABC-1234"
                                aria-required="true"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                        </div>

                        <div class="form-group">
                            <label for="color">Color (Optional)</label>
                            <input 
                                type="text" 
                                id="color" 
                                name="color" 
                                value="<?php echo htmlspecialchars($_POST['color'] ?? ''); ?>"
                                placeholder="e.g., Silver, Blue, Black"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                        </div>

                        <div class="form-actions mt-6 flex gap-3">
                            <button type="submit" class="btn btn-primary rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">Add Vehicle</button>
                            <a href="/vehicles.php" class="btn btn-secondary rounded-xl bg-slate-900 px-5 py-2.5 text-white hover:bg-slate-800">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Vehicle List -->
            <div class="vehicles-section">
                <div class="section-header flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Your Vehicles</h2>
                    <a href="/vehicles.php?action=add" class="btn btn-primary rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        <i class="fas fa-plus"></i> Add Vehicle
                    </a>
                </div>

                <?php if (empty($vehicles)): ?>
                    <div class="empty-state">
                        <i class="fas fa-car"></i>
                        <p>No vehicles registered yet. Add your first vehicle to start booking services!</p>
                        <a href="/vehicles.php?action=add" class="btn btn-primary">Add Vehicle</a>
                    </div>
                <?php else: ?>
                    <div class="vehicles-grid mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <div class="vehicle-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="vehicle-header border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
                                </div>
                                <div class="vehicle-body mt-3 text-sm text-slate-600">
                                    <p><strong>Year:</strong> <?php echo htmlspecialchars($vehicle['year']); ?></p>
                                    <p><strong>License Plate:</strong> <?php echo htmlspecialchars($vehicle['license_plate']); ?></p>
                                    <?php if ($vehicle['color']): ?>
                                        <p><strong>Color:</strong> <?php echo htmlspecialchars($vehicle['color']); ?></p>
                                    <?php endif; ?>
                                    <p class="vehicle-added mt-3 text-xs text-slate-400">
                                        <small>Added: <?php echo date('M j, Y', strtotime($vehicle['created_at'])); ?></small>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
