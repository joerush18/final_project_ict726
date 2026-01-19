<?php
/**
 * Admin Garages Management
 * View and manage all garages
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('admin');

$pdo = getDBConnection();
$errors = [];
$success = false;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $garageId = $_POST['garage_id'] ?? 0;
    $newStatus = $_POST['status'] ?? '';
    
    if ($garageId && in_array($newStatus, ['active', 'inactive'])) {
        try {
            $stmt = $pdo->prepare("UPDATE garages SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $garageId]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Failed to update garage status.';
        }
    }
}

// Get all garages
$stmt = $pdo->query("SELECT g.*, u.name as owner_name,
                     (SELECT COUNT(*) FROM services WHERE garage_id = g.id AND status = 'active') as service_count,
                     (SELECT COUNT(*) FROM bookings WHERE garage_id = g.id) as booking_count
                     FROM garages g
                     JOIN users u ON g.owner_user_id = u.id
                     ORDER BY g.created_at DESC");
$garages = $stmt->fetchAll();

$pageTitle = 'Manage Garages - Admin Panel';
$pageDescription = 'View and manage all garages';
include __DIR__ . '/../../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Manage Garages</h1>
            <p class="page-subtitle text-sm text-slate-500">View and manage all garages in the system</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                Garage status updated successfully!
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

        <!-- Garages List -->
        <div class="garages-section">
            <?php if (empty($garages)): ?>
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <p>No garages found.</p>
                </div>
            <?php else: ?>
                <div class="garages-grid mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($garages as $garage): ?>
                        <div class="garage-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="garage-header flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($garage['name']); ?></h3>
                                <span class="garage-status status-<?php echo $garage['status']; ?>">
                                    <?php echo ucfirst($garage['status']); ?>
                                </span>
                            </div>
                            <div class="garage-body mt-3 text-sm text-slate-600">
                                <p><strong>Owner:</strong> <?php echo htmlspecialchars($garage['owner_name']); ?></p>
                                <p class="garage-address flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                    <?php echo htmlspecialchars($garage['address']); ?>
                                </p>
                                <p class="garage-phone flex items-center gap-2">
                                    <i class="fas fa-phone text-blue-600"></i>
                                    <?php echo htmlspecialchars($garage['phone']); ?>
                                </p>
                                <div class="garage-stats mt-4 flex flex-wrap gap-2 text-xs text-slate-500">
                                    <span class="stat-item flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                        <i class="fas fa-wrench text-blue-600"></i>
                                        <?php echo $garage['service_count']; ?> Services
                                    </span>
                                    <span class="stat-item flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                        <i class="fas fa-calendar text-blue-600"></i>
                                        <?php echo $garage['booking_count']; ?> Bookings
                                    </span>
                                </div>
                            </div>
                            <div class="garage-footer mt-4 flex items-center justify-between">
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="garage_id" value="<?php echo $garage['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="status-select rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                        <option value="active" <?php echo $garage['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $garage['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </form>
                                <a href="/garage_detail.php?id=<?php echo $garage['id']; ?>" class="btn btn-sm btn-primary rounded-xl bg-blue-600 px-3 py-2 text-white hover:bg-blue-700">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
