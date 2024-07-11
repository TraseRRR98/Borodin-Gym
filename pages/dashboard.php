<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../loginlogout/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <a href="fighters.php" class="btn btn-primary">Manage Fighters</a>
        <a href="tournaments.php" class="btn btn-primary">Manage Tournaments</a>
        <a href="../loginlogout/logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>
