### user/orders.php

```php
<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'My Orders';

// Check if user is logged in
require_user();

$user_id = $_SESSION['user_id'];

// Get user's orders
$orders = get_orders_by_user($user_id);

include '../includes/header.php';
?>

<h1 class="mb-4">My Orders</h1>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">
        You haven't placed any orders yet.
    </div>
    <a href="dashboard.php" class="btn btn-primary">Browse Services</a>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Service</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Created</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="<?php echo $order['status'] === 'completed' ? 'table-success' : ($order['status'] === 'rejected' ? 'table-danger' : ''); ?>">
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['service_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['order_type_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo format_date($order['date_created']); ?></td>
                        <td><?php echo format_date($order['due_date']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $order['status'] === 'pending' ? 'warning' : 
                                    ($order['status'] === 'in_progress' ? 'info' : 
                                    ($order['status'] === 'completed' ? 'success' : 'danger')); 
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <a href="dashboard.php" class="btn btn-primary">Place Another Order</a>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>