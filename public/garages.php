<?php
/**
 * Garage Listing Page
 * Shows all active garages with filtering options
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDBConnection();

// Get filter parameters
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';

// Build query with filters
$query = "SELECT g.*, u.name as owner_name,
          (SELECT COUNT(*) FROM services s WHERE s.garage_id = g.id AND s.status = 'active') as service_count
          FROM garages g
          JOIN users u ON g.owner_user_id = u.id
          WHERE g.status = 'active'";

$params = [];

if (!empty($search)) {
    $query .= " AND (g.name LIKE ? OR g.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($location)) {
    $query .= " AND g.address LIKE ?";
    $params[] = "%$location%";
}

$query .= " ORDER BY g.name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$garages = $stmt->fetchAll();

$pageTitle = 'Browse Garages - Car Service Portal';
$pageDescription = 'Find and compare car service garages in your area';
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Browse Garages</h1>
            <p class="page-subtitle text-sm text-slate-500">Find trusted garages for your car service needs</p>
        </div>

        <!-- Filters -->
        <div class="filters-section rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" action="" class="filters-form grid gap-4 md:grid-cols-3">
                <div class="filter-group">
                    <label for="search" class="text-sm font-medium text-slate-700">Search Garages</label>
                    <input
                        type="text" 
                        id="search" 
                        name="search" 
                        placeholder="Search by name or description..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                </div>
                <div class="filter-group">
                    <label for="location" class="text-sm font-medium text-slate-700">Location</label>
                    <input
                        type="text" 
                        id="location" 
                        name="location" 
                        placeholder="City or area..."
                        value="<?php echo htmlspecialchars($location); ?>"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                </div>
                <div class="filter-actions flex items-end gap-3">
                    <button type="submit" class="btn btn-primary rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Filter</button>
                    <a href="/garages.php" class="btn btn-secondary rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">Clear</a>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="results-section">
            <?php if (empty($garages)): ?>
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>No garages found matching your criteria.</p>
                    <a href="/garages.php" class="btn btn-primary">View All Garages</a>
                </div>
            <?php else: ?>
                <div class="results-count text-sm text-slate-500">
                    <p>Found <strong><?php echo count($garages); ?></strong> garage(s)</p>
                </div>
                
                <div class="garages-grid mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <?php foreach ($garages as $garage): ?>
                        <div class="garage-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="garage-header flex items-center justify-between border-b border-slate-100 pb-3">
                                <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($garage['name']); ?></h3>
                                <span class="garage-status status-active text-xs">Active</span>
                            </div>
                            <div class="garage-body text-sm text-slate-600">
                                <p class="garage-owner flex items-center gap-2">
                                    <i class="fas fa-user text-blue-600"></i>
                                    Owner: <?php echo htmlspecialchars($garage['owner_name']); ?>
                                </p>
                                <p class="garage-address flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                    <?php echo htmlspecialchars($garage['address']); ?>
                                </p>
                                <p class="garage-phone flex items-center gap-2">
                                    <i class="fas fa-phone text-blue-600"></i>
                                    <?php echo htmlspecialchars($garage['phone']); ?>
                                </p>
                                <?php if ($garage['email']): ?>
                                    <p class="garage-email flex items-center gap-2">
                                        <i class="fas fa-envelope text-blue-600"></i>
                                        <?php echo htmlspecialchars($garage['email']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($garage['description']): ?>
                                    <p class="garage-description mt-3 text-sm text-slate-600"><?php echo htmlspecialchars(substr($garage['description'], 0, 150)); ?>...</p>
                                <?php endif; ?>
                                <div class="garage-stats mt-4 flex items-center gap-2 text-xs text-slate-500">
                                    <span class="stat-item flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                        <i class="fas fa-wrench text-blue-600"></i>
                                        <?php echo $garage['service_count']; ?> Services
                                    </span>
                                </div>
                            </div>
                            <div class="garage-footer mt-4 flex justify-end">
                                <a href="/garage_detail.php?id=<?php echo $garage['id']; ?>" class="btn btn-primary rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">View Details & Book</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
