<?php
require_once 'includes/auth.php';
require_once 'includes/session.php';

// Log the user out
logout();

// Redirect ha login page with success message
set_session_message('success', 'You have been logged out successfully!');
redirect('login.php');
?>
