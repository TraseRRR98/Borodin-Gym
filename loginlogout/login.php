<?php
session_start();

// Include the database connection and accessible files
include('../includes/dbconnect.php');
include('../includes/accessibles.php');

// Get the form data
$user = get_safe('username');
$pass = get_safe('password');

// Load admin credentials
$admin_credentials = include('../includes/admin_credentials.php');

// Check if the provided username and password match the stored credentials
if ($user === $admin_credentials['username'] && password_verify($pass, $admin_credentials['password'])) {
    // User authenticated, set session variables
    $_SESSION['username'] = $user;
    header("Location: ../pages/dashboard.php"); // Redirect to the dashboard
} else {
    // Authentication failed
    echo "Invalid username or password";
}
?>
