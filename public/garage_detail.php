<?php
/**
 * Garage Detail Page
 * Shows garage information, services, and booking form
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$garageId = $_GET['id'] ?? 0;

if (!$garageId) {
    header('Location: /garages.php');
    exit();
}

$pdo = getDBConnection();

// Get garage details
$stmt = $pdo->prepare("SELECT g.*, u.name as owner_name 
                       FROM garages g 
                       JOIN users u ON g.owner_user_id = u.id 
                       WHERE g.id = ? AND g.status = 'active'");
$stmt->execute([$garageId]);
$garage = $stmt->fetch();

if (!$garage) {
    header('Location: /garages.php');
    exit();
}

// Get services for this garage
$stmt = $pdo->prepare("SELECT * FROM services WHERE garage_id = ? AND status = 'active' ORDER BY name ASC");
$stmt->execute([$garageId]);
$services = $stmt->fetchAll();

$pageTitle = htmlspecialchars($garage['name']) . ' - Car Service Portal';
$pageDescription = htmlspecialchars($garage['description'] ?? 'Book car services at ' . $garage['name']);
include __DIR__ . '/../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="breadcrumb text-sm text-slate-500">
            <a href="/index.php" class="text-blue-600 hover:text-blue-700">Home</a> / 
            <a href="/garages.php" class="text-blue-600 hover:text-blue-700">Garages</a> / 
            <span class="text-slate-700"><?php echo htmlspecialchars($garage['name']); ?></span>
        </div>

        <div class="garage-detail">
            <div class="garage-info rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                <div class="garage-header-large">
                    <h1 class="text-3xl font-semibold text-slate-900"><?php echo htmlspecialchars($garage['name']); ?></h1>
                    <span class="garage-status status-active text-xs">Active</span>
                </div>
                
                <div class="garage-details-grid">
                    <div class="detail-item">
                        <i class="fas fa-user text-blue-600"></i>
                        <div>
                            <strong>Owner</strong>
                            <p><?php echo htmlspecialchars($garage['owner_name']); ?></p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt text-blue-600"></i>
                        <div>
                            <strong>Address</strong>
                            <p><?php echo htmlspecialchars($garage['address']); ?></p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone text-blue-600"></i>
                        <div>
                            <strong>Phone</strong>
                            <p><a href="tel:<?php echo htmlspecialchars($garage['phone']); ?>"><?php echo htmlspecialchars($garage['phone']); ?></a></p>
                        </div>
                    </div>
                    <?php if ($garage['email']): ?>
                    <div class="detail-item">
                        <i class="fas fa-envelope text-blue-600"></i>
                        <div>
                            <strong>Email</strong>
                            <p><a href="mailto:<?php echo htmlspecialchars($garage['email']); ?>"><?php echo htmlspecialchars($garage['email']); ?></a></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($garage['description']): ?>
                    <div class="garage-description-full mt-6">
                        <h2 class="text-xl font-semibold text-slate-900">About</h2>
                        <p class="mt-3 text-sm text-slate-600"><?php echo nl2br(htmlspecialchars($garage['description'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Services Section -->
            <div class="services-section mt-8">
                <h2 class="text-2xl font-semibold text-slate-900">Available Services</h2>
                
                <?php if (empty($services)): ?>
                    <div class="empty-state">
                        <i class="fas fa-wrench"></i>
                        <p>No services available at this time.</p>
                    </div>
                <?php else: ?>
                    <div class="services-grid mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($services as $service): ?>
                            <div class="service-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <div class="service-header flex items-center justify-between border-b border-slate-100 pb-3">
                                    <h3 class="text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($service['name']); ?></h3>
                                    <span class="service-price text-sm font-semibold text-blue-600">$<?php echo number_format($service['price'], 2); ?></span>
                                </div>
                                <?php if ($service['description']): ?>
                                    <p class="service-description mt-3 text-sm text-slate-600"><?php echo htmlspecialchars($service['description']); ?></p>
                                <?php endif; ?>
                                <div class="service-meta mt-4 text-xs text-slate-500">
                                    <span class="service-duration flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1">
                                        <i class="fas fa-clock text-blue-600"></i>
                                        <?php echo $service['duration_minutes']; ?> minutes
                                    </span>
                                </div>
                                <?php if (isLoggedIn() && isCustomer()): ?>
                                    <div class="service-footer mt-4 flex justify-end">
                                        <a href="/book_service.php?garage_id=<?php echo $garageId; ?>&service_id=<?php echo $service['id']; ?>" class="btn btn-primary btn-sm rounded-xl bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Book This Service</a>
                                    </div>
                                <?php elseif (!isLoggedIn()): ?>
                                    <div class="service-footer mt-4 flex justify-end">
                                        <a href="/login.php" class="btn btn-secondary btn-sm rounded-xl bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">Login to Book</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
