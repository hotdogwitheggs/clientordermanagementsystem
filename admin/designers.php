<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'Manage Designers';

// Only admins can access
require_admin();

// Handle approve, reject, delete
if (isset($_GET['action'], $_GET['id']) && is_numeric($_GET['id'])) {
    $designer_id = (int)$_GET['id'];
    $action = $_GET['action'];

    switch ($action) {
        case 'approve':
            // Approve the designer
            $database->update('designers', ['approved' => 1], 'id = :id', ['id' => $designer_id]);

            // Also promote user's role to designer
            $user_id = $database->selectOne("SELECT user_id FROM designers WHERE id = :id", ['id' => $designer_id])['user_id'];
            $database->update('users', ['role' => 'designer'], 'id = :id', ['id' => $user_id]);

            set_session_message('success', 'Designer approved and promoted to designer role.');
            break;

        case 'reject':
            $database->delete('designers', 'id = :id', ['id' => $designer_id]);
            set_session_message('warning', 'Designer application rejected and removed.');
            break;

        case 'delete':
            $database->delete('designers', 'id = :id', ['id' => $designer_id]);
            set_session_message('danger', 'Designer deleted successfully.');
            break;
    }

    redirect('designers.php');
}

// Fetch designers and their user info
$sql = "SELECT d.*, u.username, u.email, u.first_name, u.last_name 
        FROM designers d
        JOIN users u ON d.user_id = u.id
        ORDER BY d.created_at DESC";
$designers = $database->select($sql);

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Designers</h1>

<?php display_session_message(); ?>

<?php if (empty($designers)): ?>
    <div class="alert alert-info">No designers found.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Bio</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($designers as $designer): ?>
                    <tr>
                        <td><?php echo $designer['id']; ?></td>
                        <td><?php echo htmlspecialchars($designer['username']); ?></td>
                        <td><?php echo htmlspecialchars($designer['email']); ?></td>
                        <td><?php echo htmlspecialchars($designer['first_name'] . ' ' . $designer['last_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($designer['bio'])); ?></td>
                        <td>
                            <?php if ($designer['approved']): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo format_date($designer['created_at']); ?></td>
                        <td>
                            <?php if (!$designer['approved']): ?>
                                <a href="?action=approve&id=<?php echo $designer['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <a href="?action=reject&id=<?php echo $designer['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Rejecting will remove this designer. Continue?')">Reject</a>
                            <?php else: ?>
                                <a href="?action=delete&id=<?php echo $designer['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this approved designer?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
