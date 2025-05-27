<?php
require_once __DIR__ . '/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="/graphic_design_service/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/graphic_design_service/assets/css/style.css">
    <script src="/graphic_design_service/assets/js/jquery.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/graphic_design_service/index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/graphic_design_service/index.php">Home</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/graphic_design_service/admin/dashboard.php">Admin Panel</a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'designer'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/graphic_design_service/designer/dashboard.php">Designer Dashboard</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/graphic_design_service/user/dashboard.php">Dashboard</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php
                            $fullName = 'User';
                            if (isset($_SESSION['first_name'], $_SESSION['last_name']) &&
                                is_string($_SESSION['first_name']) &&
                                is_string($_SESSION['last_name'])) {
                                $fullName = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
                            }
                            echo $fullName;
                            ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/graphic_design_service/profile.php">My Profile</a></li>
                            <?php if ($_SESSION['role'] === 'user'): ?>
                                <li><a class="dropdown-item" href="/graphic_design_service/user/orders.php">My Orders</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/graphic_design_service/logout.php">Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/graphic_design_service/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/graphic_design_service/register.php">Register</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php display_session_message(); ?>
