<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';
require_once '../includes/session.php';

$page_title = 'Project Manager';
require_designer();

$user_id = $_SESSION['user_id'];
$designer = get_designer_by_user_id($user_id);
if (!$designer) {
    set_session_message('danger', 'Designer profile not found.');
    redirect('../index.php');
}

$designer_id = $designer['id'];
$valid_statuses = ['pending', 'in_progress', 'completed', 'rejected'];
$status_filter = isset($_GET['status']) && in_array($_GET['status'], $valid_statuses) ? $_GET['status'] : '';

// Handle status update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {
    $order_id = clean_input($_POST['order_id']);
    $new_status = clean_input($_POST['new_status']);

    if (in_array($new_status, $valid_statuses)) {
        $sql = "SELECT o.id FROM orders o 
                JOIN services s ON o.service_id = s.id 
                WHERE o.id = :order_id AND s.designer_id = :designer_id";
        $ownership_check = $database->selectOne($sql, [
            'order_id' => $order_id,
            'designer_id' => $designer_id
        ]);

        if ($ownership_check) {
            if (update_order_status($order_id, $new_status)) {
                set_session_message('success', 'Order status updated successfully.');
            } else {
                set_session_message('danger', 'Failed to update order status.');
            }
        } else {
            set_session_message('danger', 'You do not have permission to update this order.');
        }
    } else {
        set_session_message('danger', 'Invalid status value.');
    }

    redirect('project_manager.php' . ($status_filter ? '?status=' . $status_filter : ''));
}

// Fetch orders
$sql = "SELECT o.*, s.name as service_name, ot.name as order_type_name, 
               u.first_name, u.last_name, u.email
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN order_types ot ON o.order_type_id = ot.id
        JOIN users u ON o.user_id = u.id
        WHERE s.designer_id = :designer_id";

$params = ['designer_id' => $designer_id];

if (!empty($status_filter)) {
    $sql .= " AND o.status = :status";
    $params['status'] = $status_filter;
}

$sql .= " ORDER BY 
          CASE 
              WHEN o.status = 'pending' THEN 1
              WHEN o.status = 'in_progress' THEN 2
              WHEN o.status = 'completed' THEN 3
              ELSE 4
          END, o.due_date ASC";

$orders = $database->select($sql, $params);

include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Project Manager</h1>
    <div class="btn-group" role="group">
        <a href="project_manager.php" class="btn btn-outline-secondary <?php echo empty($status_filter) ? 'active' : ''; ?>">All</a>
        <?php foreach ($valid_statuses as $status): ?>
            <a href="project_manager.php?status=<?php echo $status; ?>" 
               class="btn btn-outline-<?php echo get_status_button_class($status); ?> <?php echo $status_filter === $status ? 'active' : ''; ?>">
               <?php echo ucwords(str_replace('_', ' ', $status)); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        No orders found<?php echo $status_filter ? " with status: " . ucfirst($status_filter) : ''; ?>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Created</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th style="min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="<?php echo $order['status'] === 'completed' ? 'table-success' : ($order['status'] === 'rejected' ? 'table-danger' : ''); ?>">
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?><br>
                            <small><?php echo htmlspecialchars($order['email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_type_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['created_at']))); ?></td>
                        <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['due_date']))); ?></td>
                        <td><span class="badge bg-<?php echo get_status_badge_class($order['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?></span></td>
                        <td>
                            <?php if ($order['status'] === 'pending'): ?>
                                <form method="post" class="d-flex gap-1">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <button name="new_status" value="in_progress" class="btn btn-sm btn-primary">Start</button>
                                    <button name="new_status" value="rejected" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            <?php elseif ($order['status'] === 'in_progress'): ?>
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <button name="new_status" value="completed" class="btn btn-sm btn-success">Complete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<?php
// Helpers
function get_status_badge_class($status) {
    switch ($status) {
        case 'pending': return 'secondary';
        case 'in_progress': return 'info';
        case 'completed': return 'success';
        case 'rejected': return 'danger';
        default: return 'light';
    }
}

function get_status_button_class($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'in_progress': return 'info';
        case 'completed': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}
?>
