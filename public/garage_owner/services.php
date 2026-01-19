<?php
/**
 * Garage Owner Services Management
 * CRUD operations for services
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('garage_owner');

$userId = getCurrentUserId();
$pdo = getDBConnection();
$errors = [];
$success = false;
$action = $_GET['action'] ?? 'list';

// Get garage owned by this user
$stmt = $pdo->prepare("SELECT id, name FROM garages WHERE owner_user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$garage = $stmt->fetch();

if (!$garage) {
    header('Location: /dashboard.php');
    exit();
}

$garageId = $garage['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add' || $action === 'edit') {
        $serviceId = $_POST['service_id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = $_POST['price'] ?? '';
        $durationMinutes = $_POST['duration_minutes'] ?? 60;
        $status = $_POST['status'] ?? 'active';
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Service name is required';
        }
        
        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $errors[] = 'Valid price is required';
        }
        
        if (empty($durationMinutes) || !is_numeric($durationMinutes) || $durationMinutes <= 0) {
            $errors[] = 'Valid duration is required';
        }
        
        // Save service
        if (empty($errors)) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO services (garage_id, name, description, price, duration_minutes, status) 
                                           VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$garageId, $name, $description, $price, $durationMinutes, $status]);
                    $success = true;
                    $action = 'list';
                } else {
                    // Verify service belongs to this garage
                    $stmt = $pdo->prepare("SELECT id FROM services WHERE id = ? AND garage_id = ?");
                    $stmt->execute([$serviceId, $garageId]);
                    if ($stmt->fetch()) {
                        $stmt = $pdo->prepare("UPDATE services SET name = ?, description = ?, price = ?, 
                                               duration_minutes = ?, status = ? WHERE id = ? AND garage_id = ?");
                        $stmt->execute([$name, $description, $price, $durationMinutes, $status, $serviceId, $garageId]);
                        $success = true;
                        $action = 'list';
                    } else {
                        $errors[] = 'Service not found or access denied';
                    }
                }
            } catch (PDOException $e) {
                $errors[] = 'Failed to save service. Please try again.';
            }
        }
    }
}

// Get service for editing
$service = null;
if ($action === 'edit') {
    $serviceId = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND garage_id = ?");
    $stmt->execute([$serviceId, $garageId]);
    $service = $stmt->fetch();
    
    if (!$service) {
        $action = 'list';
    }
}

// Get all services for this garage
$stmt = $pdo->prepare("SELECT * FROM services WHERE garage_id = ? ORDER BY name ASC");
$stmt->execute([$garageId]);
$services = $stmt->fetchAll();

$pageTitle = 'Manage Services - Car Service Portal';
$pageDescription = 'Manage services for ' . htmlspecialchars($garage['name']);
include __DIR__ . '/../../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Manage Services</h1>
            <p class="page-subtitle text-sm text-slate-500">Garage: <?php echo htmlspecialchars($garage['name']); ?></p>
        </div>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Add/Edit Service Form -->
            <div class="form-container flex justify-center">
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        Service <?php echo $action === 'add' ? 'added' : 'updated'; ?> successfully! 
                        <a href="/garage_owner/services.php">View all services</a>
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

                    <form method="POST" action="" class="service-form w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm" novalidate>
                        <input type="hidden" name="service_id" value="<?php echo $service['id'] ?? ''; ?>">
                        
                        <div class="form-group">
                            <label for="name">Service Name <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                value="<?php echo htmlspecialchars($service['name'] ?? $_POST['name'] ?? ''); ?>"
                                required
                                placeholder="e.g., Oil Change, Full Service"
                                aria-required="true"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            >
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="4"
                                placeholder="Describe what this service includes..."
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            ><?php echo htmlspecialchars($service['description'] ?? $_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-row grid gap-4 md:grid-cols-2">
                            <div class="form-group">
                                <label for="price">Price ($) <span class="required">*</span></label>
                                <input 
                                    type="number" 
                                    id="price" 
                                    name="price" 
                                    value="<?php echo htmlspecialchars($service['price'] ?? $_POST['price'] ?? ''); ?>"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    aria-required="true"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>

                            <div class="form-group">
                                <label for="duration_minutes">Duration (minutes) <span class="required">*</span></label>
                                <input 
                                    type="number" 
                                    id="duration_minutes" 
                                    name="duration_minutes" 
                                    value="<?php echo htmlspecialchars($service['duration_minutes'] ?? $_POST['duration_minutes'] ?? 60); ?>"
                                    min="1"
                                    required
                                    aria-required="true"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="required">*</span></label>
                            <select id="status" name="status" required aria-required="true"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="active" <?php echo ($service['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($service['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="form-actions mt-6 flex gap-3">
                            <button type="submit" class="btn btn-primary rounded-xl bg-blue-600 px-5 py-2.5 text-white hover:bg-blue-700">
                                <?php echo $action === 'add' ? 'Add Service' : 'Update Service'; ?>
                            </button>
                            <a href="/garage_owner/services.php" class="btn btn-secondary rounded-xl bg-slate-900 px-5 py-2.5 text-white hover:bg-slate-800">Cancel</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Services List -->
            <div class="services-section">
                <div class="section-header flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-slate-900">Your Services</h2>
                    <a href="/garage_owner/services.php?action=add" class="btn btn-primary rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        <i class="fas fa-plus"></i> Add Service
                    </a>
                </div>

                <?php if (empty($services)): ?>
                    <div class="empty-state">
                        <i class="fas fa-wrench"></i>
                        <p>No services added yet. Add your first service to start accepting bookings!</p>
                        <a href="/garage_owner/services.php?action=add" class="btn btn-primary">Add Service</a>
                    </div>
                <?php else: ?>
                    <div class="services-list mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="service-header flex items-center justify-between border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($service['name']); ?></h3>
                                    <span class="status-badge status-<?php echo $service['status']; ?>">
                                        <?php echo ucfirst($service['status']); ?>
                                    </span>
                                </div>
                                <div class="service-body mt-3 text-sm text-slate-600">
                                    <?php if ($service['description']): ?>
                                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="service-meta mt-4 flex items-center gap-3 text-xs text-slate-500">
                                        <span class="service-price rounded-full bg-slate-100 px-3 py-1 font-semibold text-blue-600">$<?php echo number_format($service['price'], 2); ?></span>
                                        <span class="service-duration flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                            <i class="fas fa-clock text-blue-600"></i>
                                            <?php echo $service['duration_minutes']; ?> min
                                        </span>
                                    </div>
                                </div>
                                <div class="service-footer mt-4 flex justify-end">
                                    <a href="/garage_owner/services.php?action=edit&id=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary rounded-xl bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">Edit</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
