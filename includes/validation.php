<?php
function validate_username($username) {
    if (empty($username)) {
        return "Username is required";
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        return "Username must be between 3 and 50 characters";
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return "Username can only contain letters, numbers, and underscores";
    }

    if (is_username_taken($username)) {
        return "This username is already taken";
    }

    return "";
}

function validate_email($email) {
    if (empty($email)) {
        return "Email is required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }

    if (is_email_taken($email)) {
        return "This email is already registered";
    }

    return "";
}

function validate_password($password) {
    if (empty($password)) {
        return "Password is required";
    }

    if (strlen($password) < 6) {
        return "Password must be at least 6 characters";
    }

    return "";
}

function validate_confirm_password($password, $confirm_password) {
    if ($password !== $confirm_password) {
        return "Passwords do not match";
    }

    return "";
}

function validate_name($name, $field = "Name") {
    if (empty($name)) {
        return "$field is required";
    }

    if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        return "$field can only contain letters and spaces";
    }

    return "";
}

function validate_birthday($birthday) {
    if (empty($birthday)) {
        return "Birthday is required";
    }

    $date = date_create_from_format('Y-m-d', $birthday);
    if (!$date) {
        return "Invalid date format";
    }

    $now = new DateTime();
    $min_date = new DateTime();
    $min_date->modify('-100 years');
    $max_date = new DateTime();
    $max_date->modify('-13 years');

    if ($date > $now) {
        return "Birthday cannot be in the future";
    }

    if ($date < $min_date) {
        return "Birthday is too far in the past";
    }

    if ($date > $max_date) {
        return "You must be at least 13 years old";
    }

    return "";
}

function validate_service_name($name) {
    if (empty($name)) {
        return "Service name is required";
    }

    if (strlen($name) < 3 || strlen($name) > 100) {
        return "Service name must be between 3 and 100 characters";
    }

    return "";
}

function validate_description($description) {
    if (empty($description)) {
        return "Description is required";
    }

    return "";
}

function validate_image($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return "Image is required";
    }

    $upload_result = upload_image($file);

    if (!$upload_result['success']) {
        return $upload_result['message'];
    }

    return $upload_result;
}
?>