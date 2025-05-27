<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'Manage Services';
require_admin();

$designer_id = isset($_GET['designer_id']) ? intval($_GET['designer_id']) : 0;

// Fetch services with designer info
$sql = "SELECT s.*, u.first_name, u.last_name, u.username,
               (SELECT COUNT(*) FROM order_types ot WHERE ot.service_id = s.id) AS order_type_count
        FROM services s
        JOIN designers d ON s.designer_id = d.id
        JOIN users u ON d.user_id = u.id";

$params = [];
if ($designer_id > 0) {
    $sql .= " WHERE s.designer_id = :designer_id";
    $params['designer_id'] = $designer_id;
}

$sql .= " ORDER BY s.created_at DESC";

$services = $database->select($sql, $params);

// Handle delete request
if (isset($_GET['delete_service'])) {
    $service_id = intval($_GET['delete_service']);
    $database->delete('services', ['id' => $service_id]);
    set_session_message('success', 'Service deleted successfully.');
    redirect('manage_services.php' . ($designer_id ? '?designer_id=' . $designer_id : ''));
}

include '../includes/header.php';
?>

<h1 class="mb-4">Manage Services</h1>

<?php if ($designer_id): ?>
    <p class="mb-3">
        <a href="manage_services.php" class="btn btn-outline-secondary btn-sm">View All Services</a>
    </p>
<?php endif; ?>

<?php if (empty($services)): ?>
    <div class="alert alert-info">No services found<?php echo $designer_id ? ' for this designer.' : '.'; ?></div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Service</th>
                    <th>Description</th>
                    <th>Designer</th>
                    <th>Order Types</th>
                    <th>Created</th>
                    <th style="min-width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                    <tr>
                        <td><?php echo $service['id']; ?></td>
                        <td><?php echo htmlspecialchars($service['name']); ?></td>
                        <td>
                            <span class="d-inline-block text-truncate" style="max-width: 240px;">
                                <?php echo htmlspecialchars($service['description']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($service['first_name'] . ' ' . $service['last_name']); ?>
                            <br><small class="text-muted">@<?php echo htmlspecialchars($service['username']); ?></small>
                        </td>
                        <td><span class="badge bg-info"><?php echo $service['order_type_count']; ?> types</span></td>
                        <td><?php echo date('M d, Y', strtotime($service['created_at'])); ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="../designer/edit_service.php?service_id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="manage_services.php?delete_service=<?php echo $service['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this service? All related order types will be removed.')">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
