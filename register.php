<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';
require_once 'includes/session.php';

$page_title = 'Register';

$username = $email = $password = $confirm_password = $first_name = $last_name = $birthday = "";
$username_err = $email_err = $password_err = $confirm_password_err = $first_name_err = $last_name_err = $birthday_err = "";

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $password = clean_input($_POST['password']);
    $confirm_password = clean_input($_POST['confirm_password']);
    $first_name = clean_input($_POST['first_name']);
    $last_name = clean_input($_POST['last_name']);
    $birthday = clean_input($_POST['birthday']);

    $username_err = validate_username($username);
    $email_err = validate_email($email);
    $password_err = validate_password($password);
    $confirm_password_err = validate_confirm_password($password, $confirm_password);
    $first_name_err = validate_name($first_name, 'First Name');
    $last_name_err = validate_name($last_name, 'Last Name');
    $birthday_err = validate_birthday($birthday);

    if (empty($username_err) && empty($email_err) && empty($password_err) &&
        empty($confirm_password_err) && empty($first_name_err) && empty($last_name_err) && empty($birthday_err)) {
        
        $user_id = create_user($username, $email, $password, $first_name, $last_name, $birthday);
        if ($user_id) {
            set_session_message('success', 'Registration successful. You can now login.');
            redirect('login.php');
        } else {
            set_session_message('danger', 'Registration failed. Please try again.');
        }
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow mt-4 mb-5">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0">Create an Account</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control <?php echo $first_name_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control <?php echo $last_name_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control <?php echo $username_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>" required>
                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control <?php echo $email_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" required>
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="birthday" class="form-label">Birthday</label>
                        <input type="date" name="birthday" id="birthday" class="form-control <?php echo $birthday_err ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($birthday); ?>" required>
                        <div class="invalid-feedback"><?php echo $birthday_err; ?></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control <?php echo $password_err ? 'is-invalid' : ''; ?>" required>
                            <div class="invalid-feedback"><?php echo $password_err; ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo $confirm_password_err ? 'is-invalid' : ''; ?>" required>
                            <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                        </div>
                    </div>

                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-success">Register</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>