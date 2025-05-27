<?php
require_once '../../includes/session.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (!is_logged_in() || $_SESSION['role'] !== 'designer') {
    set_session_message('Unauthorized access.', 'danger');
    redirect('../login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_session_message('Invalid order type ID.', 'danger');
    redirect('dashboard.php');
}

$order_type_id = $_GET['id'];

// Get the order type to verify ownership and delete its image
$sql = "SELECT ot.id, ot.service_id, ot.image, s.designer_id 
        FROM order_types ot
        JOIN services s ON ot.service_id = s.id
        WHERE ot.id = :id";
$order_type = $database->selectOne($sql, ['id' => $order_type_id]);

if (!$order_type || $order_type['designer_id'] != get_designer_id($_SESSION['user_id'])) {
    set_session_message('Order type not found or access denied.', 'danger');
    redirect('dashboard.php');
}

// Delete image file if exists
if (!empty($order_type['image']) && file_exists('../../assets/images/order_types/' . $order_type['image'])) {
    unlink('../../assets/images/order_types/' . $order_type['image']);
}

// Delete from database
$database->query("DELETE FROM order_types WHERE id = :id", ['id' => $order_type_id]);

set_session_message('Order type deleted successfully.', 'success');
redirect("edit_service.php?id=" . $order_type['service_id']);
?>
