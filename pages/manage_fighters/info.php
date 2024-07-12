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

    // Fetch fighter details
    $stmt = $conn->prepare("SELECT * FROM fighters WHERE ID = ?");
    $stmt->bind_param("i", $fighterId);
    $stmt->execute();
    $result = $stmt->get_result();
    $fighter = $result->fetch_assoc();
    $stmt->close();

    // Fetch fighter's fight history with opponent names and IDs
    $stmt = $conn->prepare("
        SELECT fr.*, 
               f1.ID AS fighterID, 
               f2.ID AS opponentID, 
               f2.firstName AS opponentFirstName, 
               f2.lastName AS opponentLastName 
        FROM fightrecords fr
        LEFT JOIN fighters f1 ON (fr.firstFighterID = f1.ID OR fr.secondFighterID = f1.ID)
        LEFT JOIN fighters f2 ON (fr.firstFighterID = f2.ID OR fr.secondFighterID = f2.ID)
        WHERE f1.ID = ? AND f2.ID != ?
    ");
    $stmt->bind_param("ii", $fighterId, $fighterId);
    $stmt->execute();
    $fightHistory = $stmt->get_result();
    $stmt->close();
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
                        <a class="nav-link" href="../../loginlogout/logout.php">Logout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fighters.php">Back to Fighters</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-5">
        <?php if ($fighter): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h1 class="card-title"><?php echo htmlspecialchars($fighter['firstName'] . ' ' . $fighter['lastName']); ?></h1>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <?php
                                    $photoPath = $fighter['photo'] ? htmlspecialchars($fighter['photo']) : '../uploads/default.jpg';
                                    ?>
                                    <img src="<?php echo $photoPath; ?>" class="img-fluid mb-3 profile-photo" alt="Fighter Photo">
                                </div>
                                <div class="col-md-8">
                                    <p><strong>Preferred Name:</strong> <?php echo htmlspecialchars($fighter['preferredName']); ?></p>
                                    <p><strong>Weight Class:</strong> <?php echo htmlspecialchars($fighter['weightClass']); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($fighter['address']); ?></p>
                                    <p><strong>City:</strong> <?php echo htmlspecialchars($fighter['city']); ?></p>
                                    <p><strong>Country:</strong> <?php echo htmlspecialchars($fighter['country']); ?></p>
                                    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($fighter['dateOfBirth']); ?></p>
                                    <p><strong>Club:</strong> <?php echo htmlspecialchars($fighter['club']); ?></p>
                                    <p><strong>Date Started Training:</strong> <?php echo htmlspecialchars($fighter['dateStartTraining']); ?></p>
                                    <p><strong>Wins:</strong> <?php echo htmlspecialchars($fighter['wins']); ?></p>
                                    <p><strong>Losses:</strong> <?php echo htmlspecialchars($fighter['losses']); ?></p>
                                    <p><strong>Draws:</strong> <?php echo htmlspecialchars($fighter['draws']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <h2 class="mb-3">Fight History</h2>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped">
                            <thead class="table-dark">
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
                                <?php while ($fight = $fightHistory->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fight['fightDate']); ?></td>
                                        <td><?php echo htmlspecialchars($fight['place']); ?></td>
                                        <td><?php echo htmlspecialchars($fight['organization']); ?></td>
                                        <td>
                                            <?php
                                            if ($fight['firstFighterID'] == $fighterId) {
                                                $opponentID = $fight['secondFighterID'];
                                            } else {
                                                $opponentID = $fight['firstFighterID'];
                                            }
                                            echo "<a href='info.php?id={$opponentID}'>" . htmlspecialchars($fight['opponentFirstName'] . ' ' . $fight['opponentLastName']) . "</a>";
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($fight['weight']); ?></td>
                                        <td><?php echo htmlspecialchars($fight['age']); ?></td>
                                        <td><?php echo htmlspecialchars($fight['result']); ?></td>
                                        <td>
                                            <?php
                                            if ($fight['firstFighterID'] == $fighterId) {
                                                if ($fight['firstFighterWon']) {
                                                    echo "<span class='badge bg-success'>Win</span>";
                                                } elseif ($fight['secondFighterWon']) {
                                                    echo "<span class='badge bg-danger'>Loss</span>";
                                                } else {
                                                    echo "<span class='badge bg-warning text-dark'>Draw</span>";
                                                }
                                            } else {
                                                if ($fight['secondFighterWon']) {
                                                    echo "<span class='badge bg-success'>Win</span>";
                                                } elseif ($fight['firstFighterWon']) {
                                                    echo "<span class='badge bg-danger'>Loss</span>";
                                                } else {
                                                    echo "<span class='badge bg-warning text-dark'>Draw</span>";
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>Fighter not found.</p>
        <?php endif; ?>
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
</body>
</html>

<?php
// Close the database connection at the end of the file
$conn->close();
?>