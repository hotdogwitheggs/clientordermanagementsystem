<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';
require_once 'includes/auth.php';
require_once 'includes/session.php';

$page_title = 'Login';

$email = $password = "";
$email_err = $password_err = "";

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } elseif (is_designer()) {
        redirect('designer/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Email or Username input
    $email = clean_input($_POST["email"]);
    if (empty($email)) {
        $email_err = "Please enter your email or username.";
    }

    $password = clean_input($_POST["password"]);
    if (empty($password)) {
        $password_err = "Please enter your password.";
    }

    if (empty($email_err) && empty($password_err)) {
        if (login($email, $password)) {
            if (is_admin()) {
                redirect('admin/dashboard.php');
            } elseif (is_designer()) {
                redirect('designer/dashboard.php');
            } else {
                redirect('user/dashboard.php');
            }
        } else {
            $password_err = "Invalid email/username or password.";
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow mt-5">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0">Login to Your Account</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email or Username</label>
                        <input type="text" name="email" id="email" class="form-control <?php echo $email_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control <?php echo $password_err ? 'is-invalid' : ''; ?>" required>
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Login</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>