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
    $fighterId = $_POST['ID'];
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
    $deletePhoto = isset($_POST['deletePhoto']) ? true : false;

    // Handle file upload
    $photoPath = NULL;
    if ($deletePhoto) {
        // Delete the existing photo
        $currentPhoto = $_POST['currentPhoto'];
        if ($currentPhoto && file_exists($currentPhoto)) {
            unlink($currentPhoto);
        }
        $photoPath = NULL; // Reset photo path to null
    } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
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
    } else {
        $photoPath = $_POST['currentPhoto'] ? $_POST['currentPhoto'] : NULL;
    }

    // Use prepared statements to update data
    $stmt = $conn->prepare("UPDATE fighters SET firstName = ?, lastName = ?, preferredName = ?, weightClass = ?, address = ?, city = ?, country = ?, dateOfBirth = ?, club = ?, dateStartTraining = ?, photo = ? WHERE ID = ?");
    $stmt->bind_param("sssssssssssi", $firstName, $lastName, $preferredName, $weightClass, $address, $city, $country, $dateOfBirth, $club, $dateStartTraining, $photoPath, $fighterId);

    if ($stmt->execute()) {
        echo "Fighter updated successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to fighters page
    header("Location: fighters.php");
    exit();
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
    <title>Edit Fighter - Borodin Gym</title>
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
                <h1>Edit Fighter</h1>
                <form action="edit_fighter.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="ID" value="<?php echo $fighter['ID']; ?>">
                    <input type="hidden" name="currentPhoto" value="<?php echo htmlspecialchars($fighter['photo'] ?? ''); ?>">
                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($fighter['firstName']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($fighter['lastName']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="preferredName" class="form-label">Preferred Name</label>
                        <input type="text" class="form-control" id="preferredName" name="preferredName" value="<?php echo htmlspecialchars($fighter['preferredName']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="weightClass" class="form-label">Weight Class</label>
                        <input type="text" class="form-control" id="weightClass" name="weightClass" value="<?php echo htmlspecialchars($fighter['weightClass']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($fighter['address']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($fighter['city']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($fighter['country']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="dateOfBirth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" value="<?php echo htmlspecialchars($fighter['dateOfBirth']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="club" class="form-label">Club</label>
                        <input type="text" class="form-control" id="club" name="club" value="<?php echo htmlspecialchars($fighter['club']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="dateStartTraining" class="form-label">Date Started Training</label>
                        <input type="date" class="form-control" id="dateStartTraining" name="dateStartTraining" value="<?php echo htmlspecialchars($fighter['dateStartTraining']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo">
                        <?php if ($fighter['photo']): ?>
                            <img src="<?php echo htmlspecialchars($fighter['photo']); ?>" class="img-fluid mt-2 profile-photo" alt="Current Photo">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="deletePhoto" name="deletePhoto">
                                <label class="form-check-label" for="deletePhoto">
                                    Delete current photo
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Fighter</button>
                </form>
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
</body>
</html>
