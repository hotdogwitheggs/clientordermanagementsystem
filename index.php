<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = 'Home';

// Fetch all services with designer info
$sql = "SELECT s.*, u.first_name, u.last_name, u.username, 
        (SELECT COUNT(*) FROM order_types WHERE service_id = s.id) as order_type_count
        FROM services s 
        JOIN designers d ON s.designer_id = d.id 
        JOIN users u ON d.user_id = u.id 
        WHERE d.approved = 1
        ORDER BY s.created_at DESC";
$services = $database->select($sql);

include 'includes/header.php';
?>

<div class="jumbotron bg-light p-5 rounded">
    <h1 class="display-4">Welcome to <?php echo SITE_NAME; ?></h1>
    <p class="lead">Find the perfect designer for your graphic design needs. Browse through our services and start your project today!</p>
    <?php if (!is_logged_in()): ?>
        <a href="register.php" class="btn btn-primary btn-lg">Register Now</a>
        <a href="login.php" class="btn btn-outline-primary btn-lg">Login</a>
    <?php endif; ?>
</div>

<h2 class="mt-5 mb-3">Featured Services</h2>

<div class="row">
    <?php if (empty($services)): ?>
        <div class="col-12">
            <div class="alert alert-info">No services available yet.</div>
        </div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                    </div>
                    <div class="card-footer">
                        <small class="text-muted">By <?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?> (<?php echo htmlspecialchars($service['username']); ?>)</small><br>
                        <small class="text-muted"><?php echo $service['order_type_count']; ?> Order Types</small><br>
                        <a href="user/order.php?service_id=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary mt-2">Order Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>