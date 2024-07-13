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
    $paymentData = $paymentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
    <title>Fighter Info - Borodin Gym</title>
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
                <h1>Fighter Info: <?php echo htmlspecialchars($fighter['firstName'] . ' ' . $fighter['lastName']); ?></h1>
                <div class="card mb-3">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <?php
                            $photoPath = $fighter['photo'] ? htmlspecialchars($fighter['photo']) : '../uploads/default.jpg';
                            ?>
                            <img src="<?php echo $photoPath; ?>" class="img-fluid mb-3 profile-photo" alt="Fighter Photo">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <h5 class="card-title">Profile Details</h5>
                                <p class="card-text"><strong>First Name:</strong> <?php echo htmlspecialchars($fighter['firstName']); ?></p>
                                <p class="card-text"><strong>Last Name:</strong> <?php echo htmlspecialchars($fighter['lastName']); ?></p>
                                <p class="card-text"><strong>Preferred Name:</strong> <?php echo htmlspecialchars($fighter['preferredName']); ?></p>
                                <p class="card-text"><strong>Weight Class:</strong> <?php echo htmlspecialchars($fighter['weightClass']); ?></p>
                                <p class="card-text"><strong>Address:</strong> <?php echo htmlspecialchars($fighter['address']); ?></p>
                                <p class="card-text"><strong>City:</strong> <?php echo htmlspecialchars($fighter['city']); ?></p>
                                <p class="card-text"><strong>Country:</strong> <?php echo htmlspecialchars($fighter['country']); ?></p>
                                <p class="card-text"><strong>Date of Birth:</strong> <?php echo htmlspecialchars($fighter['dateOfBirth']); ?></p>
                                <p class="card-text"><strong>Club:</strong> <?php echo htmlspecialchars($fighter['club']); ?></p>
                                <p class="card-text"><strong>Date Started Training:</strong> <?php echo htmlspecialchars($fighter['dateStartTraining']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#paymentHistory" aria-expanded="false" aria-controls="paymentHistory">
                    View Payment History
                </button>
                <div class="collapse" id="paymentHistory">
                    <div class="card card-body">
                        <h5 class="card-title">Payment History</h5>
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
                                <?php if (!empty($paymentData)): ?>
                                    <?php foreach ($paymentData as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['ID']; ?></td>
                                            <td><?php echo $payment['amount']; ?></td>
                                            <td><?php echo $payment['paymentDate']; ?></td>
                                            <td><?php echo $payment['nextPaymentDate']; ?></td>
                                            <td><?php echo htmlspecialchars($payment['coachName']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No payment history available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <button class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#addPaymentForm" aria-expanded="false" aria-controls="addPaymentForm">
                            Add Payment
                        </button>
                        <div class="collapse" id="addPaymentForm">
                            <div class="card card-body">
                                <form action="info.php?id=<?php echo $fighterId; ?>" method="post">
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
                        <h5 class="card-title">Payment Year Visualization</h5>
                        <div class="progress-container mb-3">
                            <div class="progress-bar" id="progress-bar"></div>
                        </div>
                    </div>
                </div>
                <h5 class="card-title">Fight History</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fight Date</th>
                            <th>Place</th>
                            <th>Organization</th>
                            <th>Opponent</th>
                            <th>Weight</th>
                            <th>Age</th>
                            <th>Result</th>
                            <th>Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch fight history
                        $fightHistoryStmt = $conn->prepare("SELECT fightrecords.*, f1.firstName AS firstFighterName, f1.lastName AS firstFighterLastName, f2.firstName AS secondFighterName, f2.lastName AS secondFighterLastName FROM fightrecords JOIN fighters f1 ON fightrecords.firstFighterID = f1.ID JOIN fighters f2 ON fightrecords.secondFighterID = f2.ID WHERE fightrecords.firstFighterID = ? OR fightrecords.secondFighterID = ? ORDER BY fightDate DESC");
                        $fightHistoryStmt->bind_param("ii", $fighterId, $fighterId);
                        $fightHistoryStmt->execute();
                        $fightHistory = $fightHistoryStmt->get_result();

                        while ($fight = $fightHistory->fetch_assoc()):
                            $opponentName = $fight['firstFighterID'] == $fighterId ? $fight['secondFighterName'] . ' ' . $fight['secondFighterLastName'] : $fight['firstFighterName'] . ' ' . $fight['firstFighterLastName'];
                            $outcome = $fight['firstFighterID'] == $fighterId ? ($fight['firstFighterWon'] ? 'Win' : ($fight['secondFighterWon'] ? 'Loss' : 'Draw')) : ($fight['secondFighterWon'] ? 'Win' : ($fight['firstFighterWon'] ? 'Loss' : 'Draw'));
                            $outcomeClass = $outcome === 'Win' ? 'text-success' : ($outcome === 'Loss' ? 'text-danger' : 'text-warning');
                        ?>
                        <tr>
                            <td><?php echo $fight['fightDate']; ?></td>
                            <td><?php echo htmlspecialchars($fight['place']); ?></td>
                            <td><?php echo htmlspecialchars($fight['organization']); ?></td>
                            <td><a href="info.php?id=<?php echo $fight['firstFighterID'] == $fighterId ? $fight['secondFighterID'] : $fight['firstFighterID']; ?>"><?php echo $opponentName; ?></a></td>
                            <td><?php echo htmlspecialchars($fight['weight']); ?></td>
                            <td><?php echo htmlspecialchars($fight['age']); ?></td>
                            <td><?php echo htmlspecialchars($fight['result']); ?></td>
                            <td class="<?php echo $outcomeClass; ?>"><?php echo $outcome; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php $fightHistoryStmt->close(); ?>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center p-4 mt-5">
        <p>Follow us on social media:</p>
        <a href="#" class="text-white me-4"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-white me-4"><i class="fab fa-twitter"></i></a>
        <a href="#" class="text-white me-4"><i class="fab fa-instagram"></i></a>
        <a href="#" class="text-white me-4"><i class="fab fa-linkedin-in"></i></a>
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

    const payments = <?php echo json_encode($paymentData); ?>;
    const currentYear = new Date().getFullYear();
    const totalDaysInYear = 365 + (currentYear % 4 === 0 ? 1 : 0); // Adjust for leap years
    const progressBarContainer = document.querySelector('.progress-container');

    // Helper function to create ticks and labels
    function createTicks() {
        for (let i = 1; i <= 12; i++) {
            const tick = document.createElement('div');
            tick.classList.add('progress-tick');
            tick.style.left = `${(i / 12) * 100}%`;
            progressBarContainer.appendChild(tick);

            const label = document.createElement('div');
            label.classList.add('progress-label');
            label.style.left = `${(i / 12) * 100}%`;
            label.innerText = new Date(currentYear, i - 1, 1).toLocaleString('default', { month: 'short' });
            progressBarContainer.appendChild(label);
        }
    }

    // Create the progress bar segments
    function createProgressBarSegments(payments) {
        const now = new Date();
        let segmentStart = new Date(currentYear, 0, 1); // Start from January 1st of the current year

        payments.forEach(payment => {
            const paymentDate = new Date(payment.paymentDate);
            const nextPaymentDate = new Date(payment.nextPaymentDate);

            // Ensure we handle payments correctly within the year
            if (paymentDate.getFullYear() === currentYear) {
                // Calculate overdue segment if there's a gap between segmentStart and paymentDate
                if (segmentStart < paymentDate) {
                    const overdueDays = (paymentDate - segmentStart) / (1000 * 60 * 60 * 24);
                    const overdueSegmentWidth = (overdueDays / totalDaysInYear) * 100;

                    const overdueSegment = document.createElement('div');
                    overdueSegment.classList.add('progress-bar');
                    overdueSegment.style.backgroundColor = '#f44336';
                    overdueSegment.style.left = `${(segmentStart - new Date(currentYear, 0, 1)) / (1000 * 60 * 60 * 24) / totalDaysInYear * 100}%`;
                    overdueSegment.style.width = `${overdueSegmentWidth}%`;
                    overdueSegment.innerText = 'Overdue';
                    progressBarContainer.appendChild(overdueSegment);

                    segmentStart = paymentDate;
                }

                const daysCovered = (nextPaymentDate - segmentStart) / (1000 * 60 * 60 * 24);
                const segmentWidth = (daysCovered / totalDaysInYear) * 100;

                const paidSegment = document.createElement('div');
                paidSegment.classList.add('progress-bar');
                paidSegment.style.backgroundColor = '#4caf50';
                paidSegment.style.left = `${(segmentStart - new Date(currentYear, 0, 1)) / (1000 * 60 * 60 * 24) / totalDaysInYear * 100}%`;
                paidSegment.style.width = `${segmentWidth}%`;
                progressBarContainer.appendChild(paidSegment);

                segmentStart = nextPaymentDate;
            }
        });

        // Create the due or overdue segment from the last payment's next payment date to the current date
        if (segmentStart < now) {
            const daysCovered = (now - segmentStart) / (1000 * 60 * 60 * 24);
            const segmentWidth = (daysCovered / totalDaysInYear) * 100;

            const dueSegment = document.createElement('div');
            dueSegment.classList.add('progress-bar');
            dueSegment.style.left = `${(segmentStart - new Date(currentYear, 0, 1)) / (1000 * 60 * 60 * 24) / totalDaysInYear * 100}%`;
            dueSegment.style.width = `${segmentWidth}%`;

            if (now > segmentStart) {
                dueSegment.style.backgroundColor = '#f44336';
                dueSegment.innerText = 'Overdue';
            } else {
                dueSegment.style.backgroundColor = '#ff9800';
                dueSegment.innerText = 'Due';
            }
            progressBarContainer.appendChild(dueSegment);
        }
    }

    createTicks();
    createProgressBarSegments(payments);
});
</script>
</body>
</html>
