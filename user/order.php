<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'Order Service';
require_user();

$user_id = $_SESSION['user_id'];
$designer_id = $service_id = $order_type_id = 0;

// Handle parameters
if (isset($_GET['designer_id'])) {
    $designer_id = intval($_GET['designer_id']);
    $designer = $database->selectOne("SELECT d.*, u.first_name, u.last_name FROM designers d JOIN users u ON d.user_id = u.id WHERE d.id = :id AND d.approved = 1", ['id' => $designer_id]);

    if (!$designer) {
        set_session_message('danger', 'Designer not found.');
        redirect('dashboard.php');
    }

    $services = get_services_by_designer($designer_id);
    $page_title = 'Services by ' . $designer['first_name'] . ' ' . $designer['last_name'];
} elseif (isset($_GET['service_id'])) {
    $service_id = intval($_GET['service_id']);
    $service = $database->selectOne("SELECT s.*, d.id AS designer_id, u.first_name, u.last_name FROM services s JOIN designers d ON s.designer_id = d.id JOIN users u ON d.user_id = u.id WHERE s.id = :id AND d.approved = 1", ['id' => $service_id]);

    if (!$service) {
        set_session_message('danger', 'Service not found.');
        redirect('dashboard.php');
    }

    $designer_id = $service['designer_id'];
    $designer = ['id' => $designer_id, 'first_name' => $service['first_name'], 'last_name' => $service['last_name']];
    $order_types = get_order_types_by_service($service_id);
    $page_title = 'Order ' . $service['name'];
} elseif (isset($_GET['order_type_id'])) {
    $order_type_id = intval($_GET['order_type_id']);
    $order_type = $database->selectOne("SELECT ot.*, s.id AS service_id, s.name AS service_name, d.id AS designer_id, u.first_name, u.last_name FROM order_types ot JOIN services s ON ot.service_id = s.id JOIN designers d ON s.designer_id = d.id JOIN users u ON d.user_id = u.id WHERE ot.id = :id AND d.approved = 1", ['id' => $order_type_id]);

    if (!$order_type) {
        set_session_message('danger', 'Order type not found.');
        redirect('dashboard.php');
    }

    $service_id = $order_type['service_id'];
    $designer_id = $order_type['designer_id'];
    $service = ['id' => $service_id, 'name' => $order_type['service_name']];
    $designer = ['id' => $designer_id, 'first_name' => $order_type['first_name'], 'last_name' => $order_type['last_name']];
    $page_title = 'Order ' . $order_type['name'];
} else {
    set_session_message('danger', 'Invalid request.');
    redirect('dashboard.php');
}

// Process order submission
$title = $due_date = "";
$title_err = $due_date_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $title = clean_input($_POST["title"]);
    $due_date = clean_input($_POST["due_date"]);
    $service_id = intval($_POST['service_id']);
    $order_type_id = intval($_POST['order_type_id']);

    if (empty($title)) {
        $title_err = "Please enter a title.";
    }

    if (empty($due_date) || !validate_due_date($due_date)) {
        $due_date_err = "Please select a valid due date (today or later).";
    }

    if (empty($title_err) && empty($due_date_err)) {
        $inserted = $database->insert('orders', [
            'user_id' => $user_id,
            'service_id' => $service_id,
            'order_type_id' => $order_type_id,
            'title' => $title,
            'due_date' => $due_date,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($inserted) {
            set_session_message('success', 'Order placed successfully!');
            redirect('orders.php');
        } else {
            set_session_message('danger', 'Failed to place the order.');
        }
    }
}

include '../includes/header.php';
?>

<h1 class="mb-4"><?php echo htmlspecialchars($page_title); ?></h1>

<?php if (isset($services)): ?>
    <h5>Select a Service</h5>
    <div class="row">
        <?php foreach ($services as $s): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if (!empty($s['image'])): ?>
                        <img src="<?php echo UPLOAD_URL . htmlspecialchars($s['image']); ?>" class="card-img-top" alt="Service Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($s['name']); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($s['description'])); ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="order.php?service_id=<?php echo $s['id']; ?>" class="btn btn-primary w-100">Select Service</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php elseif (isset($order_types)): ?>
    <h5>Select Order Type for "<?php echo htmlspecialchars($service['name']); ?>"</h5>
    <div class="row">
        <?php foreach ($order_types as $type): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if (!empty($type['image'])): ?>
                        <img src="<?php echo UPLOAD_URL . htmlspecialchars($type['image']); ?>" class="card-img-top" alt="Order Type Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($type['name']); ?></h5>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($type['description'])); ?></p>
                    </div>
                    <div class="card-footer">
                        <a href="order.php?order_type_id=<?php echo $type['id']; ?>" class="btn btn-primary w-100">Select Type</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php elseif (isset($order_type)): ?>
    <h5 class="mb-3">Placing Order for: <strong><?php echo htmlspecialchars($order_type['name']); ?></strong></h5>
    <form method="post">
        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
        <input type="hidden" name="order_type_id" value="<?php echo $order_type_id; ?>">

        <div class="mb-3">
            <label for="title" class="form-label">Order Title</label>
            <input type="text" name="title" id="title" class="form-control <?php echo !empty($title_err) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title); ?>" required>
            <div class="invalid-feedback"><?php echo $title_err; ?></div>
        </div>

        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
                <input type="date" name="due_date" id="due_date" class="form-control <?php echo !empty($due_date_err) ? 'is-invalid' : ''; ?>"value="<?php echo htmlspecialchars(!empty($due_date) ? $due_date : date('Y-m-d')); ?>" equired onkeydown="return false;">            
                <div class="invalid-feedback"><?php echo $due_date_err; ?></div>
        </div>
        <div class="d-flex">
            <button type="submit" name="place_order" class="btn btn-success">Place Order</button>
            <a href="dashboard.php" class="btn btn-outline-secondary ms-2">Cancel</a>
        </div>
    </form>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>