<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

$page_title = 'User Dashboard';
require_user();

$user_id = $_SESSION['user_id'];

// Get user info
$sql = "SELECT * FROM users WHERE id = :id";
$user = $database->selectOne($sql, ['id' => $user_id]);

// Get approved designers and their services
$sql = "SELECT d.*, u.username, u.email, u.first_name, u.last_name, 
        COUNT(DISTINCT s.id) as service_count
        FROM designers d
        JOIN users u ON d.user_id = u.id
        LEFT JOIN services s ON d.id = s.designer_id
        WHERE d.approved = 1
        GROUP BY d.id
        ORDER BY u.first_name, u.last_name";
$designers = $database->select($sql);

include '../includes/header.php';
?>

<h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Your Information</div>
            <div class="card-body">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
                <a href="../profile.php" class="btn btn-outline-primary">Edit Profile</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">Quick Links</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="orders.php" class="btn btn-primary">My Orders</a>
                    <a href="../index.php" class="btn btn-outline-secondary">Browse Services</a>
                </div>
            </div>
        </div>
    </div>
</div>

<h2 class="mb-3">Choose a Designer</h2>

<div class="row">
<?php if (empty($designers)): ?>
    <div class="col-12">
        <div class="alert alert-info">No designers available at the moment.</div>
    </div>
<?php else: ?>
    <?php foreach ($designers as $designer): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <?php
                    // Get first service with image from this designer
                    $image_sql = "SELECT image FROM services 
                                  WHERE designer_id = :designer_id AND image IS NOT NULL AND image != '' 
                                  ORDER BY id ASC LIMIT 1";
                    $img = $database->selectOne($image_sql, ['designer_id' => $designer['id']]);
                    if ($img && $img['image']):
                    ?>
                        <img src="<?php echo UPLOAD_URL . $img['image']; ?>" class="img-fluid mb-3 rounded shadow-sm" alt="Service preview">
                    <?php endif; ?>

                    <h5 class="card-title"><?php echo htmlspecialchars($designer['first_name'] . ' ' . $designer['last_name']); ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">@<?php echo htmlspecialchars($designer['username']); ?></h6>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($designer['bio'])); ?></p>
                    <p class="card-text"><small class="text-muted"><?php echo $designer['service_count']; ?> service(s) available</small></p>
                </div>
                <div class="card-footer">
                    <a href="order.php?designer_id=<?php echo $designer['id']; ?>" class="btn btn-primary">View Services</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
