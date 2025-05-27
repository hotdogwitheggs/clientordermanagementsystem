<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_admin();

$page_title = 'Manage Users';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $sql = "DELETE FROM users WHERE id = :id AND role != 'admin'";
    $deleted = $database->execute($sql, ['id' => $user_id]);

    if ($deleted) {
        set_session_message('success', 'User deleted successfully.');
    } else {
        set_session_message('danger', 'Failed to delete user.');
    }

    redirect('users.php');
}

// Fetch all non-admin users
$sql = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
$users = $database->select($sql);

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Users</h1>

<?php if (empty($users)): ?>
    <div class="alert alert-info">No users found.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo ucfirst($user['role']); ?></span></td>
                        <td><span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                        <td><?php echo format_date($user['created_at']); ?></td>
                        <td>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
