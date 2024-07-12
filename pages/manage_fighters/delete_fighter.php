<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../loginlogout/login.html");
    exit();
}

include('../../includes/dbconnect.php');
include('../../includes/accessibles.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['id'])) {
    $fighterId = $_GET['id'];

    // Delete the fighter from the database
    $stmt = $conn->prepare("DELETE FROM fighters WHERE ID = ?");
    $stmt->bind_param("i", $fighterId);

    if ($stmt->execute()) {
        echo "Fighter deleted successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to fighters page
    header("Location: fighters.php");
    exit();
} else {
    echo "Invalid fighter ID.";
}
?>
