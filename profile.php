<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';
require_once 'includes/session.php';

$page_title = 'My Profile';

// Check if user naka log in
require_login();

// Get user details
$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

// Get designer details if user is a designer
$designer = null;
if ($_SESSION['role'] === 'designer') {
    $designer = get_designer_by_user_id($user_id);
}

// Initialize variables
$username = $user['username'];
$email = $user['email'];
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$birthday = $user['birthday'];
$bio = $designer ? $designer['bio'] : '';

$username_err = $email_err = $first_name_err = $last_name_err = $birthday_err = $bio_err = "";
$current_password = $new_password = $confirm_password = "";
$current_password_err = $new_password_err = $confirm_password_err = "";

// Process form data when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        // Validate first name
        $first_name = clean_input($_POST["first_name"]);
        $first_name_err = validate_name($first_name, "First name");
        
        // Validate last name
        $last_name = clean_input($_POST["last_name"]);
        $last_name_err = validate_name($last_name, "Last name");
        
        // Validate birthday
        $birthday = clean_input($_POST["birthday"]);
        $birthday_err = validate_birthday($birthday);
        
        // Validate designer bio if applicable
        if ($_SESSION['role'] === 'designer') {
            $bio = clean_input($_POST["bio"]);
            if (empty($bio)) {
                $bio_err = "Please provide a short bio for your designer profile.";
            }
        }
        
        // Check input errors before updating database
        if (empty($first_name_err) && empty($last_name_err) && empty($birthday_err) && empty($bio_err)) {
            // Update user data
            $data = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'birthday' => $birthday
            ];
            
            if (update_user($user_id, $data)) {
                // Update designer data if applicable
                if ($_SESSION['role'] === 'designer' && $designer) {
                    $database->update('designers', ['bio' => $bio], 'user_id = :user_id', ['user_id' => $user_id]);
                }
                
                // Update session variables
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                
                set_session_message('success', 'Your profile has been updated successfully!');
                redirect('profile.php');
            } else {
                set_session_message('danger', 'Something went wrong. Please try again later.');
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Validate current password
        $current_password = clean_input($_POST["current_password"]);
        if (empty($current_password)) {
            $current_password_err = "Please enter your current password.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $current_password_err = "Current password is incorrect.";
        }
        
        // Validate new password
        $new_password = clean_input($_POST["new_password"]);
        $new_password_err = validate_password($new_password);
        
        // Validate confirm password
        $confirm_password = clean_input($_POST["confirm_password"]);
        $confirm_password_err = validate_confirm_password($new_password, $confirm_password);
        
        // Check input errors before updating database
        if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            if ($database->update('users', ['password' => $hashed_password], 'id = :id', ['id' => $user_id])) {
                set_session_message('success', 'Your password has been changed successfully!');
                redirect('profile.php');
            } else {
                set_session_message('danger', 'Something went wrong. Please try again later.');
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header">
                <h4>Profile Menu</h4>
            </div>
            <div class="list-group list-group-flush">
                <a href="#profile-info" class="list-group-item list-group-item-action" data-bs-toggle="tab">Profile Information</a>
                <a href="#change-password" class="list-group-item list-group-item-action" data-bs-toggle="tab">Change Password</a>
                <?php if ($_SESSION['role'] === 'user'): ?>
                    <a href="user/orders.php" class="list-group-item list-group-item-action">My Orders</a>
                <?php elseif ($_SESSION['role'] === 'designer'): ?>
                    <a href="designer/dashboard.php" class="list-group-item list-group-item-action">Designer Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="profile-info">
                <div class="card">
                    <div class="card-header">
                        <h4>Profile Information</h4>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" disabled>
                                    <small class="form-text text-muted">Username cannot be changed.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
                                    <small class="form-text text-muted">Email cannot be changed.</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" name="first_name" id="first_name" class="form-control <?php echo !empty($first_name_err) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
                                    <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" name="last_name" id="last_name" class="form-control <?php echo !empty($last_name_err) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
                                    <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="birthday" class="form-label">Birthday</label>
                                    <input type="date" name="birthday" id="birthday" class="form-control <?php echo !empty($birthday_err) ? 'is-invalid' : ''; ?>" value="<?php echo $birthday; ?>">
                                    <div class="invalid-feedback"><?php echo $birthday_err; ?></div>
                                </div>
                            </div>
                            
                            <?php if ($_SESSION['role'] === 'designer'): ?>
                            <div class="mb-3">
                                <label for="bio" class="form-label">Designer Bio</label>
                                <textarea name="bio" id="bio" rows="4" class="form-control <?php echo !empty($bio_err) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($bio); ?></textarea>
                                <div class="invalid-feedback"><?php echo $bio_err; ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid">
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="change-password">
                <div class="card">
                    <div class="card-header">
                        <h4>Change Password</h4>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control <?php echo !empty($current_password_err) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $current_password_err; ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control <?php echo !empty($new_password_err) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo !empty($confirm_password_err) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Activate tabs based on URL hash or show default tab
$(document).ready(function() {
    let hash = window.location.hash;
    if (hash) {
        $('a[href="' + hash + '"]').tab('show');
    }
    
    // Change hash for tab
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
    });
});
</script>

<?php include 'includes/footer.php'; ?>
