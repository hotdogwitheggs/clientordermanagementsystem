<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'All Orders';
require_admin();

$valid_statuses = ['pending', 'in_progress', 'completed', 'rejected'];
$status_filter = isset($_GET['status']) && in_array($_GET['status'], $valid_statuses) ? $_GET['status'] : '';

// Fetch orders with full joins
$sql = "SELECT o.*, 
               s.name AS service_name,
               ot.name AS order_type_name,
               u.first_name AS user_first, u.last_name AS user_last,
               du.first_name AS designer_first, du.last_name AS designer_last
        FROM orders o
        JOIN services s ON o.service_id = s.id
        JOIN order_types ot ON o.order_type_id = ot.id
        JOIN users u ON o.user_id = u.id
        JOIN designers d ON s.designer_id = d.id
        JOIN users du ON d.user_id = du.id";

$params = [];

if ($status_filter) {
    $sql .= " WHERE o.status = :status";
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

<h1 class="mb-4">All Orders</h1>

<div class="mb-3">
    <div class="btn-group" role="group">
        <a href="orders.php" class="btn btn-outline-secondary <?php echo !$status_filter ? 'active' : ''; ?>">All</a>
        <?php foreach ($valid_statuses as $status): ?>
            <a href="orders.php?status=<?php echo $status; ?>" 
               class="btn btn-outline-<?php echo get_status_button_class($status); ?> <?php echo $status_filter === $status ? 'active' : ''; ?>">
                <?php echo ucwords(str_replace('_', ' ', $status)); ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        No orders found<?php echo $status_filter ? ' with status: ' . ucfirst($status_filter) : ''; ?>.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Title</th>
                    <th>Service</th>
                    <th>Type</th>
                    <th>User</th>
                    <th>Designer</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="<?php echo $order['status'] === 'completed' ? 'table-success' : ($order['status'] === 'rejected' ? 'table-danger' : ''); ?>">
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_type_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_first'] . ' ' . $order['user_last']); ?></td>
                        <td><?php echo htmlspecialchars($order['designer_first'] . ' ' . $order['designer_last']); ?></td>
                        <td><?php echo format_date($order['due_date']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo get_status_badge_class($order['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<?php
function get_status_badge_class($status) {
    return match ($status) {
        'pending' => 'warning',
        'in_progress' => 'info',
        'completed' => 'success',
        'rejected' => 'danger',
        default => 'secondary',
    };
}

function get_status_button_class($status) {
    return get_status_badge_class($status);
}
?>
