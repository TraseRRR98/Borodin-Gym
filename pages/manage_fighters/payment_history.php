<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../loginlogout/login.html");
    exit();
}

include('../../includes/dbconnect.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fighterId = $_POST['fighterID'];
    $amount = $_POST['amount'];
    $paymentDate = $_POST['paymentDate'];
    $nextPaymentDate = $_POST['nextPaymentDate'];
    $coachID = $_POST['coachID'];

    // Use prepared statements to insert payment data
    $stmt = $conn->prepare("INSERT INTO payments (fighterID, amount, paymentDate, nextPaymentDate, coachID) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idssi", $fighterId, $amount, $paymentDate, $nextPaymentDate, $coachID);

    if ($stmt->execute()) {
        echo "Payment added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

if (isset($_GET['id'])) {
    $fighterId = $_GET['id'];

    // Fetch fighter details
    $stmt = $conn->prepare("SELECT * FROM fighters WHERE ID = ?");
    $stmt->bind_param("i", $fighterId);
    $stmt->execute();
    $result = $stmt->get_result();
    $fighter = $result->fetch_assoc();
    $stmt->close();

    // Fetch payment history
    $paymentStmt = $conn->prepare("SELECT payments.*, coaches.name as coachName FROM payments JOIN coaches ON payments.coachID = coaches.ID WHERE fighterID = ? ORDER BY paymentDate DESC");
    $paymentStmt->bind_param("i", $fighterId);
    $paymentStmt->execute();
    $payments = $paymentStmt->get_result();
    $paymentStmt->close();
} else {
    echo "Invalid fighter ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Borodin Gym</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/styles.css" rel="stylesheet">
    <!-- Alternative Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../index.php">Borodin Gym</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="fighters.php">Back to Fighters</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../loginlogout/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1>Payment History for <?php echo htmlspecialchars($fighter['firstName'] . ' ' . $fighter['lastName']); ?></h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Payment Date</th>
                            <th>Next Payment Date</th>
                            <th>Coach</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = $payments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $payment['ID']; ?></td>
                                <td><?php echo $payment['amount']; ?></td>
                                <td><?php echo $payment['paymentDate']; ?></td>
                                <td><?php echo $payment['nextPaymentDate']; ?></td>
                                <td><?php echo htmlspecialchars($payment['coachName']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#addPaymentForm" aria-expanded="false" aria-controls="addPaymentForm">
                    Add Payment
                </button>
                <div class="collapse" id="addPaymentForm">
                    <div class="card card-body">
                        <form action="payment_history.php?id=<?php echo $fighterId; ?>" method="post">
                            <input type="hidden" name="fighterID" value="<?php echo $fighterId; ?>">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="paymentDate" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" id="paymentDate" name="paymentDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="nextPaymentDate" class="form-label">Next Payment Date</label>
                                <input type="date" class="form-control" id="nextPaymentDate" name="nextPaymentDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="coachID" class="form-label">Coach</label>
                                <select class="form-control" id="coachID" name="coachID" required>
                                    <!-- Populate with coaches from the database -->
                                    <?php
                                    $coaches = $conn->query("SELECT ID, name FROM coaches");
                                    while ($coach = $coaches->fetch_assoc()) {
                                        echo "<option value='{$coach['ID']}'>{$coach['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center p-4 mt-5">
        <p>Follow us on social media:</p>
        <a href="#" class="text-white me-4"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-white me-4"><i class="fab fa-twitter"></i></a>
        <a href="#" class="text-white me-4"><i class="fab fa-instagram"></i></a>
        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
        <p class="mt-3">© 2024 Borodin Gym. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the current date in EST
            let estDate = new Date().toLocaleString("en-US", { timeZone: "America/New_York" });
            let today = new Date(estDate).toISOString().split('T')[0];
            document.getElementById('paymentDate').value = today;

            // Get the date one month from now in EST
            let nextMonthDate = new Date(estDate);
            nextMonthDate.setMonth(nextMonthDate.getMonth() + 1);
            let nextMonth = nextMonthDate.toISOString().split('T')[0];
            document.getElementById('nextPaymentDate').value = nextMonth;
        });
    </script>
</body>
</html>
