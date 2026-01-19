<?php
/**
 * Admin Users Management
 * View and manage all users
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';

requireRole('admin');

$pdo = getDBConnection();
$errors = [];
$success = false;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $userId = $_POST['user_id'] ?? 0;
    $newStatus = $_POST['status'] ?? '';
    
    if ($userId && in_array($newStatus, ['active', 'inactive'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $userId]);
            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Failed to update user status.';
        }
    }
}

// Get all users
$roleFilter = $_GET['role'] ?? 'all';
$query = "SELECT u.*, 
          (SELECT COUNT(*) FROM garages WHERE owner_user_id = u.id) as garage_count,
          (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as booking_count
          FROM users u";

$params = [];
if ($roleFilter !== 'all') {
    $query .= " WHERE u.role = ?";
    $params[] = $roleFilter;
}

$query .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users - Admin Panel';
$pageDescription = 'View and manage all system users';
include __DIR__ . '/../../includes/header.php';
?>

<main class="main-content py-12">
    <div class="container max-w-6xl mx-auto px-4">
        <div class="page-header text-center">
            <h1 class="page-title text-3xl font-semibold text-slate-900">Manage Users</h1>
            <p class="page-subtitle text-sm text-slate-500">View and manage all system users</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                User status updated successfully!
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

        <!-- Role Filter -->
        <div class="filters-section rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="status-filters flex flex-wrap gap-2">
                <a href="/admin/users.php?role=all" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $roleFilter === 'all' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">All</a>
                <a href="/admin/users.php?role=customer" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $roleFilter === 'customer' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Customers</a>
                <a href="/admin/users.php?role=garage_owner" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $roleFilter === 'garage_owner' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Garage Owners</a>
                <a href="/admin/users.php?role=admin" class="filter-btn rounded-full px-4 py-1 text-sm <?php echo $roleFilter === 'admin' ? 'active bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>">Admins</a>
            </div>
        </div>

        <!-- Users List -->
        <div class="users-section">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No users found.</p>
                </div>
            <?php else: ?>
                <div class="users-table-container overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="data-table w-full text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Garages</th>
                                <th>Bookings</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['garage_count']; ?></td>
                                    <td><?php echo $user['booking_count']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="status-select rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                                <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
