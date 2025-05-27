<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'Manage Users';
require_admin();

// Handle actions: ban/unban/delete
if (isset($_GET['action'], $_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'ban') {
        $database->update('users', ['status' => 'banned'], ['id' => $user_id]);
        set_session_message('success', 'User banned successfully.');
    } elseif ($action === 'unban') {
        $database->update('users', ['status' => 'active'], ['id' => $user_id]);
        set_session_message('success', 'User unbanned successfully.');
    } elseif ($action === 'delete') {
        $database->delete('users', ['id' => $user_id]);
        set_session_message('success', 'User deleted permanently.');
    }

    redirect('users.php');
}

// Fetch users with roles and statuses
$sql = "SELECT u.*, 
            CASE 
                WHEN u.role = 'admin' THEN 'Admin'
                WHEN EXISTS (SELECT 1 FROM designers d WHERE d.user_id = u.id) THEN 'Designer'
                ELSE 'User'
            END AS role_label
        FROM users u
        ORDER BY u.created_at DESC";

$users = $database->select($sql);

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Users</h1>

<?php if (empty($users)): ?>
    <div class="alert alert-info">No registered users found.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th style="min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="<?php echo $user['status'] === 'banned' ? 'table-danger' : ''; ?>">
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo get_role_badge_class($user['role_label']); ?>">
                                <?php echo $user['role_label']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if ($user['status'] === 'active'): ?>
                                    <a href="users.php?action=ban&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Ban</a>
                                <?php elseif ($user['status'] === 'banned'): ?>
                                    <a href="users.php?action=unban&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">Unban</a>
                                <?php endif; ?>
                                <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user permanently?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<?php
// Helper for badge color
function get_role_badge_class($role) {
    return match ($role) {
        'Admin' => 'dark',
        'Designer' => 'info',
        'User' => 'secondary',
        default => 'light'
    };
}
?>
