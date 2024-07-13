<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../loginlogout/login.html");
    exit();
}

include('../../includes/dbconnect.php');
include('../../includes/accessibles.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $preferredName = $_POST['preferredName'];
    $weightClass = $_POST['weightClass'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $dateOfBirth = $_POST['dateOfBirth'];
    $club = $_POST['club'];
    $dateStartTraining = $_POST['dateStartTraining'];

    // Payment information
    $initialPayment = $_POST['initialPayment'];
    $paymentDate = $_POST['paymentDate'];
    $nextPaymentDate = $_POST['nextPaymentDate'];
    $coachID = $_POST['coachID'];

    // Handle file upload
    $photoPath = NULL;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $uploadsDir = '../../uploads/';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }
        $photoTmpPath = $_FILES['photo']['tmp_name'];
        $photoName = basename($_FILES['photo']['name']);
        $photoPath = $uploadsDir . $photoName;
        if (!move_uploaded_file($photoTmpPath, $photoPath)) {
            echo "Failed to move uploaded file.";
            exit();
        }
    }

    // Use prepared statements to insert data
    $stmt = $conn->prepare("INSERT INTO fighters (firstName, lastName, preferredName, weightClass, address, city, country, dateOfBirth, club, dateStartTraining, photo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $firstName, $lastName, $preferredName, $weightClass, $address, $city, $country, $dateOfBirth, $club, $dateStartTraining, $photoPath);

    if ($stmt->execute()) {
        $fighterID = $stmt->insert_id;

        // Insert payment record
        $paymentStmt = $conn->prepare("INSERT INTO payments (fighterID, amount, paymentDate, nextPaymentDate, coachID) VALUES (?, ?, ?, ?, ?)");
        $paymentStmt->bind_param("idssi", $fighterID, $initialPayment, $paymentDate, $nextPaymentDate, $coachID);

        if ($paymentStmt->execute()) {
            echo "New fighter and initial payment added successfully";
        } else {
            echo "Error: " . $paymentStmt->error;
        }

        $paymentStmt->close();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Fighters - Borodin Gym</title>
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
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1>Manage Fighters</h1>
                <p>Here you can add new fighters and view all existing fighters.</p>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFighterModal">Add New Fighter</button>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <h2>All Fighters</h2>
                <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search fighters...">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Preferred Name</th>
                                <th>Club</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="fighterTableBody">
                            <?php
                            $result = $conn->query("SELECT ID, firstName, lastName, preferredName, club FROM fighters");
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$row['ID']}</td>
                                    <td>{$row['firstName']}</td>
                                    <td>{$row['lastName']}</td>
                                    <td>{$row['preferredName']}</td>
                                    <td>{$row['club']}</td>
                                    <td>
                                        <a href='info.php?id={$row['ID']}' class='btn btn-info btn-sm mb-1'>Info</a>
                                        <a href='edit_fighter.php?id={$row['ID']}' class='btn btn-warning btn-sm mb-1'>Edit</a>
                                        <a href='delete_fighter.php?id={$row['ID']}' class='btn btn-danger btn-sm mb-1' onclick=\"return confirm('Are you sure you want to delete this fighter?');\">Delete</a>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for Adding New Fighter -->
    <div class="modal fade" id="addFighterModal" tabindex="-1" aria-labelledby="addFighterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="fighters.php" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFighterModalLabel">Add New Fighter</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Fighter Information Fields -->
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                        <div class="mb-3">
                            <label for="preferredName" class="form-label">Preferred Name</label>
                            <input type="text" class="form-control" id="preferredName" name="preferredName">
                        </div>
                        <div class="mb-3">
                            <label for="weightClass" class="form-label">Weight Class</label>
                            <input type="text" class="form-control" id="weightClass" name="weightClass" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address">
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country">
                        </div>
                        <div class="mb-3">
                            <label for="dateOfBirth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" required>
                        </div>
                        <div class="mb-3">
                            <label for="club" class="form-label">Club</label>
                            <input type="text" class="form-control" id="club" name="club">
                        </div>
                        <div class="mb-3">
                            <label for="dateStartTraining" class="form-label">Date Started Training</label>
                            <input type="date" class="form-control" id="dateStartTraining" name="dateStartTraining">
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo">
                        </div>
                        <!-- Payment Information Fields -->
                        <div class="mb-3">
                            <label for="initialPayment" class="form-label">Initial Payment Amount</label>
                            <input type="number" class="form-control" id="initialPayment" name="initialPayment" required>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Fighter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center p-4">
        <p>Follow us on social media:</p>
        <a href="#" class="text-white me-4"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-white me-4"><i class="fab fa-twitter"></i></a>
        <a href="#" class="text-white me-4"><i class="fab fa-instagram"></i></a>
        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
        <p class="mt-3">© 2024 Borodin Gym. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let searchBar = document.getElementById('searchBar');
    let tBody = document.getElementById('fighterTableBody');

    searchBar.addEventListener('input', function() {
        let searchValue = searchBar.value.toLowerCase();
        let fighterRows = tBody.getElementsByTagName('tr');
        let regExp = new RegExp(searchValue, 'i');

        for (let row of fighterRows) {
            let matchFound = false;
            let fighterCols = row.getElementsByTagName('td');
            for(let col of fighterCols){
                if(col.innerHTML.search(regExp) !== -1 && !col.classList.contains('fighterTableActions')){
                    matchFound = true;
                    break;
                }
            }
            row.hidden = !matchFound;
        }
    });
</script>
</body>
</html>


<?php
// Close the database connection at the end of the file
$conn->close();
?>