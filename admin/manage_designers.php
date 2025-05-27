<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'Manage Designers';
require_admin();

// Handle approval/denial
if (isset($_GET['action'], $_GET['id'])) {
    $designer_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        $database->update('designers', ['approved' => 1], ['id' => $designer_id]);
        set_session_message('success', 'Designer approved.');
    } elseif ($action === 'deny') {
        $database->update('designers', ['approved' => 0], ['id' => $designer_id]);
        set_session_message('success', 'Designer marked as pending.');
    }

    redirect('manage_designers.php');
}

// Fetch all designers with user info and service count
$sql = "SELECT d.*, u.first_name, u.last_name, u.username, u.email,
            (SELECT COUNT(*) FROM services s WHERE s.designer_id = d.id) AS service_count
        FROM designers d
        JOIN users u ON d.user_id = u.id
        ORDER BY d.created_at DESC";

$designers = $database->select($sql);

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Designers</h1>

<?php if (empty($designers)): ?>
    <div class="alert alert-info">No designers registered yet.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Bio</th>
                    <th>Services</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th style="min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($designers as $designer): ?>
                    <tr class="<?php echo !$designer['approved'] ? 'table-warning' : ''; ?>">
                        <td><?php echo $designer['id']; ?></td>
                        <td>
                            <?php echo htmlspecialchars($designer['first_name'] . ' ' . $designer['last_name']); ?><br>
                            <small class="text-muted">@<?php echo htmlspecialchars($designer['username']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($designer['email']); ?></td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 220px;">
                                <?php echo htmlspecialchars($designer['bio'] ?? ''); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $designer['service_count']; ?> services</span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $designer['approved'] ? 'success' : 'secondary'; ?>">
                                <?php echo $designer['approved'] ? 'Approved' : 'Pending'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($designer['created_at'])); ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <?php if (!$designer['approved']): ?>
                                    <a href="manage_designers.php?action=approve&id=<?php echo $designer['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <?php else: ?>
                                    <a href="manage_designers.php?action=deny&id=<?php echo $designer['id']; ?>" class="btn btn-sm btn-warning">Set Pending</a>
                                <?php endif; ?>
                                <a href="manage_services.php?designer_id=<?php echo $designer['id']; ?>" class="btn btn-sm btn-outline-primary">View Services</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
