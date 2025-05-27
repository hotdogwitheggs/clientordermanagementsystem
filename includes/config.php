<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'graphic_design_service');

// Website settings
define('SITE_NAME', 'VAL Creative Studios');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/graphic_design_service/assets/uploads/');
define('UPLOAD_URL', '/graphic_design_service/assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Session configuration
define('SESSION_LIFETIME', 7200); // 2 hours
?>
