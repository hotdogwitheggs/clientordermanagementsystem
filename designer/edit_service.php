<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';
require_once '../includes/session.php';

$page_title = 'Edit Service';
require_designer();

$user_id = $_SESSION['user_id'];
$designer = get_designer_by_user_id($user_id);
if (!$designer) {
    set_session_message('danger', 'Designer profile not found.');
    redirect('../index.php');
}
$designer_id = $designer['id'];

// Handle delete service
if (isset($_GET['delete_service'])) {
    $delete_id = intval($_GET['delete_service']);
    if (delete_service($delete_id, $designer_id)) {
        set_session_message('success', 'Service and associated order types deleted.');
    } else {
        set_session_message('danger', 'You do not have permission to delete this service.');
    }
    redirect('dashboard.php');
}

// Handle delete order type
if (isset($_GET['delete_order_type'])) {
    $type_id = intval($_GET['delete_order_type']);
    if (delete_order_type($type_id, $designer_id)) {
        set_session_message('success', 'Order type deleted.');
    } else {
        set_session_message('danger', 'You do not have permission to delete this order type.');
    }
    redirect("edit_service.php?service_id=$service_id");
}

// Get service
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
$service = get_service_by_id($service_id);
if (!$service) {
    set_session_message('danger', 'Service not found.');
    redirect('dashboard.php');
}
if ($service['designer_id'] != $designer_id) {
    set_session_message('danger', 'You do not have permission to access this service.');
    redirect('dashboard.php');
}

$order_types = get_order_types_by_service($service_id);

// Handle update form
$name = $service['name'];
$description = $service['description'];
$name_err = $description_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $name_err = validate_service_name($name);
    $description_err = validate_description($description);

    if (empty($name_err) && empty($description_err)) {
        $updated = update_service($service_id, ['name' => $name, 'description' => $description]);
        if ($updated) {
            set_session_message('success', 'Service updated successfully.');
            redirect("edit_service.php?service_id=$service_id");
        } else {
            set_session_message('danger', 'No changes made.');
        }
    }
}

include '../includes/header.php';
?>

<h1>Edit Service</h1>
<form method="post">
    <div class="mb-3">
        <label for="name" class="form-label">Service Name</label>
        <input type="text" name="name" class="form-control <?= $name_err ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($name) ?>" required>
        <div class="invalid-feedback"><?= $name_err ?></div>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" class="form-control <?= $description_err ? 'is-invalid' : '' ?>" required><?= htmlspecialchars($description) ?></textarea>
        <div class="invalid-feedback"><?= $description_err ?></div>
    </div>
    <button type="submit" name="update_service" class="btn btn-primary">Save</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
</form>

<hr>
<h3>Order Types</h3>
<a href="add_order_type.php?service_id=<?= $service_id ?>" class="btn btn-success mb-3">Add Order Type</a>

<?php if (empty($order_types)): ?>
    <div class="alert alert-info">No order types for this service.</div>
<?php else: ?>
    <div class="row">
        <?php foreach ($order_types as $type): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <?php if (!empty($type['image'])): ?>
                        <img src="<?= UPLOAD_URL . htmlspecialchars($type['image']) ?>" class="card-img-top" alt="Order Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($type['name']) ?></h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($type['description'])) ?></p>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="edit_order_type.php?type_id=<?= $type['id'] ?>&service_id=<?= $service_id ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="edit_service.php?delete_order_type=<?= $type['id'] ?>&service_id=<?= $service_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this order type?')">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<a href="edit_service.php?delete_service=<?= $service_id ?>" class="btn btn-danger mt-4" onclick="return confirm('Are you sure you want to delete this service and its order types?')">Delete Service</a>

<?php include '../includes/footer.php'; ?>