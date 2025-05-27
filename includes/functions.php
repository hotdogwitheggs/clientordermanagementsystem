<?php
require_once 'db.php';
require_once 'config.php';

// --- USER FUNCTIONS ---

function get_user_by_id($id) {
    global $database;
    return $database->selectOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);
}

function get_user_by_email($email) {
    global $database;
    return $database->selectOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);
}

function get_user_by_username($username) {
    global $database;
    return $database->selectOne("SELECT * FROM users WHERE username = :username", ['username' => $username]);
}

function is_username_taken($username) {
    return get_user_by_username($username) ? true : false;
}

function is_email_taken($email) {
    return get_user_by_email($email) ? true : false;
}

function create_user($username, $email, $password, $first_name, $last_name, $birthday, $role = 'user') {
    global $database;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    return $database->insert('users', [
        'username' => $username,
        'email' => $email,
        'password' => $hashed_password,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'birthday' => $birthday,
        'role' => $role
    ]);
}

function update_user($id, $data) {
    global $database;
    return $database->update('users', $data, 'id = :id', ['id' => $id]);
}

function delete_user($id) {
    global $database;
    return $database->delete('users', 'id = :id', ['id' => $id]);
}

// --- DESIGNER FUNCTIONS ---

function get_designer_by_user_id($user_id) {
    global $database;
    return $database->selectOne("SELECT * FROM designers WHERE user_id = :user_id", ['user_id' => $user_id]);
}

function create_designer($user_id, $bio = '') {
    global $database;
    return $database->insert('designers', [
        'user_id' => $user_id,
        'bio' => $bio
    ]);
}

function approve_designer($user_id) {
    global $database;
    $designer = get_designer_by_user_id($user_id);
    if ($designer) {
        $database->update('designers', ['approved' => true], 'user_id = :user_id', ['user_id' => $user_id]);
        $database->update('users', ['role' => 'designer'], 'id = :id', ['id' => $user_id]);
        return true;
    }
    return false;
}

function revoke_designer($user_id) {
    global $database;
    $designer = get_designer_by_user_id($user_id);
    if ($designer) {
        $database->update('designers', ['approved' => false], 'user_id = :user_id', ['user_id' => $user_id]);
        $database->update('users', ['role' => 'user'], 'id = :id', ['id' => $user_id]);
        return true;
    }
    return false;
}

// --- SERVICE FUNCTIONS ---

function get_services() {
    global $database;
    return $database->select("SELECT s.*, u.first_name, u.last_name FROM services s JOIN designers d ON s.designer_id = d.id JOIN users u ON d.user_id = u.id");
}

function get_services_by_designer($designer_id) {
    global $database;
    return $database->select("SELECT * FROM services WHERE designer_id = :designer_id", ['designer_id' => $designer_id]);
}

function get_service_by_id($id) {
    global $database;
    return $database->selectOne("SELECT * FROM services WHERE id = :id", ['id' => $id]);
}

function create_service($designer_id, $name, $description, $image = null) {
    global $database;
    return $database->insert('services', [
        'designer_id' => $designer_id,
        'name' => $name,
        'description' => $description,
        'image' => $image
    ]);
}

function update_service($id, $data) {
    global $database;
    return $database->update('services', $data, 'id = :id', ['id' => $id]);
}

function delete_service($service_id, $designer_id = null) {
    global $database;
    $params = ['id' => $service_id];
    $sql = "SELECT * FROM services WHERE id = :id";
    if ($designer_id !== null) {
        $sql .= " AND designer_id = :designer_id";
        $params['designer_id'] = $designer_id;
    }
    $service = $database->selectOne($sql, $params);
    if (!$service) return false;

    $database->query("DELETE FROM order_types WHERE service_id = :id", ['id' => $service_id]);
    $database->query("DELETE FROM services WHERE id = :id", ['id' => $service_id]);
    return true;
}

// --- ORDER TYPE FUNCTIONS ---

function create_order_type($service_id, $name, $description, $image = '') {
    global $database;
    return $database->insert('order_types', [
        'service_id' => $service_id,
        'name' => $name,
        'description' => $description,
        'image' => $image
    ]);
}

function get_order_types_by_service($service_id) {
    global $database;
    return $database->select("SELECT * FROM order_types WHERE service_id = :service_id ORDER BY name", ['service_id' => $service_id]);
}

function get_order_type_by_id($id) {
    global $database;
    return $database->selectOne("SELECT * FROM order_types WHERE id = :id", ['id' => $id]);
}

function update_order_type($id, $data) {
    global $database;
    return $database->update('order_types', $data, 'id = :id', ['id' => $id]);
}

function delete_order_type($type_id, $designer_id = null) {
    global $database;
    $sql = "SELECT ot.*, s.designer_id FROM order_types ot JOIN services s ON ot.service_id = s.id WHERE ot.id = :id";
    $type = $database->selectOne($sql, ['id' => $type_id]);
    if (!$type || ($designer_id !== null && $type['designer_id'] != $designer_id)) return false;

    $database->query("DELETE FROM order_types WHERE id = :id", ['id' => $type_id]);
    return true;
}

// --- ORDER FUNCTIONS ---

function create_order($user_id, $service_id, $order_type_id, $title, $due_date) {
    global $database;
    $order_id = $database->generateOrderId();
    return $database->insert('orders', [
        'id' => $order_id,
        'user_id' => $user_id,
        'service_id' => $service_id,
        'order_type_id' => $order_type_id,
        'title' => $title,
        'due_date' => $due_date,
        'status' => 'pending'
    ]);
}

function get_orders_by_user($user_id) {
    global $database;
    return $database->select("SELECT o.*, s.name AS service_name, ot.name AS order_type_name 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN order_types ot ON o.order_type_id = ot.id 
        WHERE o.user_id = :user_id 
        ORDER BY o.date_created DESC", ['user_id' => $user_id]);
}

function get_orders_by_designer($designer_id) {
    global $database;
    return $database->select("SELECT o.*, s.name AS service_name, ot.name AS order_type_name 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN order_types ot ON o.order_type_id = ot.id 
        WHERE s.designer_id = :designer_id 
        ORDER BY o.date_created DESC", ['designer_id' => $designer_id]);
}

function update_order_status($id, $status) {
    global $database;
    return $database->update('orders', ['status' => $status], 'id = :id', ['id' => $id]);
}

// --- ADMIN FUNCTIONS ---

function get_all_users($exclude_admins = true) {
    global $database;
    $sql = "SELECT * FROM users";
    if ($exclude_admins) $sql .= " WHERE role != 'admin'";
    return $database->select($sql);
}

function get_pending_designer_requests() {
    global $database;
    return $database->select("SELECT d.*, u.username, u.email, u.first_name, u.last_name 
        FROM designers d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.approved = 0");
}

function get_all_designers() {
    global $database;
    return $database->select("SELECT d.*, u.username, u.email, u.first_name, u.last_name 
        FROM designers d 
        JOIN users u ON d.user_id = u.id 
        WHERE d.approved = 1");
}

// --- UTILITY FUNCTIONS ---

function format_date($date) {
    return date('F j, Y', strtotime($date));
}

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function validate_due_date($due_date) {
    if (empty($due_date)) {
        return "Due date is required";
    }

    // Try to parse both formats
    $date = DateTime::createFromFormat('d/m/Y', $due_date);
    if (!$date) {
        return "Invalid date format";
    }

    $today = new DateTime();
    $today->setTime(0, 0, 0);

    if ($date < $today) {
        return "Please select a valid due date (today or later).";
    }

    return "";
}
function upload_image($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return ['success' => false, 'message' => 'No file uploaded.'];
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return ['success' => false, 'message' => 'Invalid file type.'];

    if ($file['size'] > MAX_FILE_SIZE) return ['success' => false, 'message' => 'File is too large.'];

    $new_filename = uniqid('img_', true) . '.' . $ext;
    $target_file = UPLOAD_PATH . $new_filename;
    if (!move_uploaded_file($file['tmp_name'], $target_file)) return ['success' => false, 'message' => 'Upload failed.'];

    return ['success' => true, 'filename' => $new_filename];
}

if (!function_exists('require_designer')) {
    function require_designer() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'designer') {
            set_session_message('danger', 'You must be logged in as a designer to access that page.');
            redirect('../login.php');
            exit;
        }
    }
}
function get_designer_id($user_id) {
    global $database;
    $sql = "SELECT id FROM designers WHERE user_id = :user_id LIMIT 1";
    $result = $database->selectOne($sql, ['user_id' => $user_id]);
    return $result ? $result['id'] : null;
}
function upload_file($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $allowed_extensions = ALLOWED_EXTENSIONS;
    $max_size = MAX_FILE_SIZE;
    $upload_dir = UPLOAD_PATH;

    $filename = basename($file['name']);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_extensions)) {
        return '';
    }

    if ($file['size'] > $max_size) {
        return '';
    }

    $uniquename = uniqid('img', true) . '.' . $ext;
    $destination = $upload_dir . $unique_name;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return '';
    }

    return $unique_name;
}
?>