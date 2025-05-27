<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!is_logged_in() || $_SESSION['role'] !== 'designer') {
    redirect('../login.php');
}

$designer_id = get_designer_id($_SESSION['user_id']);
if (!$designer_id) {
    redirect('dashboard.php');
}

$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;
$service = $database->selectOne("SELECT * FROM services WHERE id = :id AND designer_id = :designer_id", [
    'id' => $service_id,
    'designer_id' => $designer_id
]);

if (!$service) {
    set_session_message("Service not found or unauthorized access.", 'danger');
    redirect('dashboard.php');
}

$name = $description = "";
$name_err = $description_err = "";
$image_path = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = clean_input($_POST["name"]);
    $description = clean_input($_POST["description"]);

    if (empty($name)) $name_err = "Order type name is required.";
    if (empty($description)) $description_err = "Description is required.";

    if (empty($name_err) && empty($description_err)) {
        $image_path = upload_file($_FILES['image']);

        $sql = "INSERT INTO order_types (service_id, name, description, image) 
                VALUES (:service_id, :name, :description, :image)";
        $database->query($sql, [
            'service_id' => $service_id,
            'name' => $name,
            'description' => $description,
            'image' => $image_path
        ]);

        set_session_message('Order type added successfully!', 'success');
        redirect('dashboard.php');
    }
}

$page_title = "Add Order Type";
include '../includes/header.php';
?>

<div class="container mt-5">
    <h2>Add Order Type for "<?php echo htmlspecialchars($service['name']); ?>"</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="card mt-4">
            <div class="card-header">Order Type Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Order Type Name</label>
                    <input type="text" name="name" class="form-control <?php echo $name_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control <?php echo $description_err ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($description); ?></textarea>
                    <div class="invalid-feedback"><?php echo $description_err; ?></div>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Order Type Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Add Order Type</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
