<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_logged_in() || $_SESSION['role'] !== 'designer') {
    redirect('../login.php');
}

$designer_id = get_designer_id($_SESSION['user_id']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_session_message("Invalid service ID.", "danger");
    redirect('dashboard.php');
}

$service_id = (int)$_GET['id'];

// Confirm the service belongs to this designer
$service = $database->selectOne("SELECT * FROM services WHERE id = :id AND designer_id = :designer_id", [
    'id' => $service_id,
    'designer_id' => $designer_id
]);

if (!$service) {
    set_session_message("Service not found or you do not have permission to delete it.", "danger");
    redirect('dashboard.php');
}

// Delete associated order types
$database->query("DELETE FROM order_types WHERE service_id = :service_id", ['service_id' => $service_id]);

// Delete the service
$database->query("DELETE FROM services WHERE id = :id", ['id' => $service_id]);

set_session_message("Service and all associated order types have been deleted.", "success");
redirect('dashboard.php');
?>
