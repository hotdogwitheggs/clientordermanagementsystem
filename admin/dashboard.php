<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'Admin Dashboard';
require_admin();

// Fetch system stats
$total_users = $database->selectOne("SELECT COUNT(*) AS total FROM users")['total'] ?? 0;
$total_designers = $database->selectOne("SELECT COUNT(*) AS total FROM designers")['total'] ?? 0;
$total_services = $database->selectOne("SELECT COUNT(*) AS total FROM services")['total'] ?? 0;
$total_orders = $database->selectOne("SELECT COUNT(*) AS total FROM orders")['total'] ?? 0;

// Orders by status
$pending_orders = $database->selectOne("SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'")['total'] ?? 0;
$in_progress_orders = $database->selectOne("SELECT COUNT(*) AS total FROM orders WHERE status = 'in_progress'")['total'] ?? 0;
$completed_orders = $database->selectOne("SELECT COUNT(*) AS total FROM orders WHERE status = 'completed'")['total'] ?? 0;
$rejected_orders = $database->selectOne("SELECT COUNT(*) AS total FROM orders WHERE status = 'rejected'")['total'] ?? 0;

include '../includes/header.php';
?>

<h1 class="mb-4">Admin Dashboard</h1>

<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h2 class="card-text"><?php echo $total_users; ?></h2>
            </div>
            <div class="card-footer">
                <a href="users.php" class="text-dark">Manage Users</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Designers</h5>
                <h2 class="card-text"><?php echo $total_designers; ?></h2>
            </div>
            <div class="card-footer">
                <a href="designers.php" class="text-dark">Manage Designers</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title">Services</h5>
                <h2 class="card-text"><?php echo $total_services; ?></h2>
            </div>
            <div class="card-footer">
                <a href="services.php" class="text-dark">View Services</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-dark h-100">
            <div class="card-body">
                <h5 class="card-title">Orders</h5>
                <h2 class="card-text"><?php echo $total_orders; ?></h2>
            </div>
            <div class="card-footer">
                <a href="orders.php" class="text-dark">View Orders</a>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-3">Orders by Status</h3>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card border-start border-4 border-warning h-100">
            <div class="card-body">
                <h6 class="text-muted">Pending</h6>
                <h3><?php echo $pending_orders; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-4 border-info h-100">
            <div class="card-body">
                <h6 class="text-muted">In Progress</h6>
                <h3><?php echo $in_progress_orders; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-4 border-success h-100">
            <div class="card-body">
                <h6 class="text-muted">Completed</h6>
                <h3><?php echo $completed_orders; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-4 border-danger h-100">
            <div class="card-body">
                <h6 class="text-muted">Rejected</h6>
                <h3><?php echo $rejected_orders; ?></h3>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
