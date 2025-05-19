<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Check if event ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-events.php");
    exit;
}

$event_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch event details
$sql = "SELECT * FROM events WHERE id = $event_id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: manage-events.php");
    exit;
}

$event = mysqli_fetch_assoc($result);

// Fetch departments and societies for dropdowns
$departments_sql = "SELECT * FROM departments";
$departments_result = mysqli_query($conn, $departments_sql);
$departments = mysqli_fetch_all($departments_result, MYSQLI_ASSOC);

$societies_sql = "SELECT * FROM societies";
$societies_result = mysqli_query($conn, $societies_sql);
$societies = mysqli_fetch_all($societies_result, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $society = mysqli_real_escape_string($conn, $_POST['society']);

    // **VALIDATION:  Crucially important!**
    $valid_department = false;
    foreach ($departments as $dept) {
        if ($dept['name'] === $department) {
            $valid_department = true;
            break;
        }
    }
    if (!$valid_department) {
        die("Error: Invalid Department selected.");
        //  Ideally, handle this more gracefully:
        //  -  Display an error message to the user on the form
        //  -  Don't proceed with the update
        //  -  Potentially log the error
    }

    // Handle image upload (basic - you might want to improve this)
    $image_url = $event['image_url']; // Keep existing if no new upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/events/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . $_FILES['image']['name'];
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
            $image_url = substr($file_path, 6); // Save relative path
        }
    }

    // Update event
    $update_sql = "UPDATE events SET 
                   title = '$title', 
                   description = '$description', 
                   event_date = '$event_date', 
                   location = '$location', 
                   category = '$category', 
                   department = '$department', 
                   society = '$society',
                   image_url = '$image_url'
                   WHERE id = $event_id";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: manage-events.php");
        exit;
    } else {
        die("Error updating event: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>

<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Edit Event</h1>

        <?php include 'admin-header.php'; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title"
                            value="<?php echo htmlspecialchars($event['title']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            required><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="event_date" class="form-label">Event Date</label>
                        <input type="date" class="form-control" id="event_date" name="event_date"
                            value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location"
                            value="<?php echo htmlspecialchars($event['location']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category"
                            value="<?php echo htmlspecialchars($event['category']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <select class="form-select" id="department" name="department">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo htmlspecialchars($dept['name']); ?>"
                                    <?php if ($event['department'] === $dept['name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="society" class="form-label">Society</label>
                        <select class="form-select" id="society" name="society">
                            <option value="">Select Society</option>
                            <?php foreach ($societies as $soc): ?>
                                <option value="<?php echo htmlspecialchars($soc['name']); ?>"
                                    <?php if ($event['society'] === $soc['name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($soc['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Event Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if ($event['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($event['image_url']); ?>" alt="Current Image"
                                style="max-width: 200px;">
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="manage-events.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Project/assets/js/main.js"></script>
</body>

</html>