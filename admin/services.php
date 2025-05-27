<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

require_admin();

$page_title = 'Manage Services';

// Get all services and their order types
$sql = "SELECT s.id as service_id, s.name as service_name, s.description as service_description, 
               s.created_at as service_created, u.first_name, u.last_name, u.username, d.id as designer_id
        FROM services s
        JOIN designers d ON s.designer_id = d.id
        JOIN users u ON d.user_id = u.id
        ORDER BY s.created_at DESC";
$services = $database->select($sql);

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Services</h1>

<?php if (empty($services)): ?>
    <div class="alert alert-info">No services have been created yet.</div>
<?php else: ?>
    <?php foreach ($services as $service): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($service['service_name']); ?></h5>
                <h6 class="card-subtitle text-muted">
                    by <?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?>
                    (@<?php echo htmlspecialchars($service['username']); ?>)
                </h6>
                <p class="mt-2"><?php echo nl2br(htmlspecialchars($service['service_description'])); ?></p>
                <p><small class="text-muted">Created on <?php echo format_date($service['service_created']); ?></small></p>

                <?php
                $order_types = $database->select(
                    "SELECT * FROM order_types WHERE service_id = :service_id ORDER BY created_at DESC",
                    ['service_id' => $service['service_id']]
                );
                ?>

                <?php if (empty($order_types)): ?>
                    <p class="text-muted">No order types defined for this service.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($order_types as $type): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <?php if (!empty($type['image']) && file_exists(UPLOAD_PATH . $type['image'])): ?>
                                        <img src="<?php echo UPLOAD_URL . $type['image']; ?>" class="card-img-top" alt="Order Type Image">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($type['name']); ?></h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($type['description'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
