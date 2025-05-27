<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'Designer Dashboard';

// Check if user is a designer
require_designer();

$user_id = $_SESSION['user_id'];

// Get designer info
$designer = get_designer_by_user_id($user_id);
if (!$designer) {
    set_session_message('danger', 'Designer profile not found.');
    redirect('../index.php');
}

// Get designer's services
$services = get_services_by_designer($designer['id']);

// Get pending orders count
$sql = "SELECT COUNT(*) as count FROM orders o 
        JOIN services s ON o.service_id = s.id 
        WHERE s.designer_id = :designer_id AND o.status = 'pending'";
$pending = $database->selectOne($sql, ['designer_id' => $designer['id']]);
$pending_count = $pending ? $pending['count'] : 0;

// Get in-progress orders count
$sql = "SELECT COUNT(*) as count FROM orders o 
        JOIN services s ON o.service_id = s.id 
        WHERE s.designer_id = :designer_id AND o.status = 'in_progress'";
$in_progress = $database->selectOne($sql, ['designer_id' => $designer['id']]);
$in_progress_count = $in_progress ? $in_progress['count'] : 0;

// Get completed orders count
$sql = "SELECT COUNT(*) as count FROM orders o 
        JOIN services s ON o.service_id = s.id 
        WHERE s.designer_id = :designer_id AND o.status = 'completed'";
$completed = $database->selectOne($sql, ['designer_id' => $designer['id']]);
$completed_count = $completed ? $completed['count'] : 0;

include '../includes/header.php';
?>

<h1 class="mb-4">Designer Dashboard</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Pending Orders</h5>
                <h2 class="card-text"><?php echo $pending_count; ?></h2>
            </div>
            <div class="card-footer">
                <a href="project_manager.php?status=pending" class="text-dark">View Pending Orders</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title">In Progress</h5>
                <h2 class="card-text"><?php echo $in_progress_count; ?></h2>
            </div>
            <div class="card-footer">
                <a href="project_manager.php?status=in_progress" class="text-dark">View In-Progress Orders</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Completed</h5>
                <h2 class="card-text"><?php echo $completed_count; ?></h2>
            </div>
            <div class="card-footer">
                <a href="project_manager.php?status=completed" class="text-dark">View Completed Orders</a>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>My Services</h2>
    <a href="service_creation.php" class="btn btn-success">Add New Service</a>
</div>

<?php if (empty($services)): ?>
    <div class="alert alert-info">
        You haven't created any services yet. Click the "Add New Service" button to get started!
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($services as $service): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                        
                        <?php
                        // Get order types count for this service
                        $sql = "SELECT COUNT(*) as count FROM order_types WHERE service_id = :service_id";
                        $count = $database->selectOne($sql, ['service_id' => $service['id']]);
                        $order_type_count = $count ? $count['count'] : 0;
                        ?>
                        <p class="card-text"><small class="text-muted"><?php echo $order_type_count; ?> order types</small></p>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="edit_service.php?service_id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="add_order_type.php?service_id=<?php echo $service['id']; ?>" class="btn btn-success">Add Order Type</a>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDelete('service', <?php echo $service['id']; ?>, '<?php echo htmlspecialchars(addslashes($service['name'])); ?>')">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteModalBody">
                Are you sure you want to delete this item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(type, id, name) {
    let modalBody = "";
    let deleteUrl = "";
    
    if (type === 'service') {
        modalBody = `Are you sure you want to delete the service "${name}"? This will also delete all associated order types.`;
        deleteUrl = `edit_service.php?delete_service=${id}`;
    } else if (type === 'order_type') {
        modalBody = `Are you sure you want to delete the order type "${name}"?`;
        deleteUrl = `edit_service.php?delete_order_type=${id}`;
    }
    
    document.getElementById('deleteModalBody').innerText = modalBody;
    document.getElementById('confirmDeleteBtn').setAttribute('href', deleteUrl);
    
    let deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php include '../includes/footer.php'; ?>
