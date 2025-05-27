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

$name = $description = "";
$name_err = $description_err = "";
$image_path = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = clean_input($_POST["name"]);
    $description = clean_input($_POST["description"]);

    if (empty($name)) $name_err = "Service name is required.";
    if (empty($description)) $description_err = "Description is required.";

    if (empty($name_err) && empty($description_err)) {
        $image_result = upload_image($_FILES['image']);
        $image_name = $image_result['success'] ? $image_result['filename'] : '';
        $data = [
            'designer_id' => $designer_id,
            'name' => $name,
            'description' => $description,
            'image' => $image_path
        ];
        $sql = "INSERT INTO services (designer_id, name, description, image) VALUES (:designer_id, :name, :description, :image)";
        $database->query($sql, $data);
        set_session_message('success', 'Service created successfully.');
        redirect('dashboard.php');
    }
}

$page_title = "Create Service";
include '../includes/header.php';
?>

<div class="container mt-5">
    <h2>Create New Service</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="card mt-4">
            <div class="card-header"><strong>Service Details</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Service Name</label>
                    <input type="text" name="name" class="form-control <?php echo $name_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                    <div class="invalid-feedback"><?php echo $name_err; ?></div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control <?php echo $description_err ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($description); ?></textarea>
                    <div class="invalid-feedback"><?php echo $description_err; ?></div>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Service Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success">Create Service</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
