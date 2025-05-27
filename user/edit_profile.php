<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';
require_once '../includes/session.php';

$page_title = 'Edit Profile';
require_user();

$user_id = $_SESSION['user_id'];

// Get current user data
$sql = "SELECT * FROM users WHERE id = :id";
$user = $database->selectOne($sql, ['id' => $user_id]);

if (!$user) {
    set_session_message('danger', 'User not found.');
    redirect('dashboard.php');
}

// Initialize fields
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$email = $user['email'];
$password = '';
$confirm_password = '';

$errors = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = clean_input($_POST['first_name']);
    $last_name = clean_input($_POST['last_name']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    $errors['first_name'] = validate_name($first_name);
    $errors['last_name'] = validate_name($last_name);
    $errors['email'] = validate_email($email, $user_id);

    if (!empty($password) || !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $errors['password'] = "Password must be at least 6 characters.";
        }
    }

    $has_errors = array_filter($errors);

    if (empty($has_errors)) {
        $update_data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email
        ];

        if (!empty($password)) {
            $update_data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $updated = $database->update('users', $update_data, ['id' => $user_id]);

        if ($updated) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['email'] = $email;

            set_session_message('success', 'Profile updated successfully.');
            redirect('dashboard.php');
        } else {
            set_session_message('danger', 'Update failed or no changes made.');
        }
    }
}

include '../includes/header.php';
?>

<h1 class="mb-4">Edit Profile</h1>

<form method="post" action="">
    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" name="first_name" id="first_name" class="form-control <?php echo $errors['first_name'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>" required>
        <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
    </div>

    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" name="last_name" id="last_name" class="form-control <?php echo $errors['last_name'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>" required>
        <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control <?php echo $errors['email'] ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New Password <small class="text-muted">(leave blank to keep current)</small></label>
        <input type="password" name="password" id="password" class="form-control <?php echo $errors['password'] ? 'is-invalid' : ''; ?>">
        <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
    </div>

    <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo $errors['confirm_password'] ? 'is-invalid' : ''; ?>">
        <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
    </div>

    <div class="d-flex">
        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
        <a href="dashboard.php" class="btn btn-outline-secondary ms-2">Cancel</a>
    </div>
</form>

<?php include '../includes/footer.php'; ?>
