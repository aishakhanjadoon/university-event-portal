<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /Project/pages/auth/login.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $society = mysqli_real_escape_string($conn, $_POST['society']);
    $created_by = $_SESSION['user_id'];
    
    // Handle image upload
    $image_url = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/events/';
        if(!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_url = '/Project/uploads/events/' . $new_filename;
        }
    }

    // Insert new event
    $sql = "INSERT INTO events (title, description, event_date, location, category, department, society, image_url, created_by) 
            VALUES ('$title', '$description', '$event_date', '$location', '$category', '$department', '$society', '$image_url', $created_by)";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: manage-events.php");
        exit;
    } else {
        $error = "Error creating event: " . mysqli_error($conn);
    }
}

// Fetch departments and societies for dropdowns
$departments_sql = "SELECT * FROM departments ORDER BY name";
$departments = mysqli_query($conn, $departments_sql);

$societies_sql = "SELECT * FROM societies ORDER BY name";
$societies = mysqli_query($conn, $societies_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4">Add New Event</h1>
        
        <?php include 'admin-header.php'; ?>

        <div class="card">
            <div class="card-body">
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="event_date" class="form-label">Event Date</label>
                            <input type="datetime-local" class="form-control" id="event_date" name="event_date" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="academic">Academic</option>
                                <option value="cultural">Cultural</option>
                                <option value="sports">Sports</option>
                                <option value="technical">Technical</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Select Department</option>
                                <?php while($dept = mysqli_fetch_assoc($departments)): ?>
                                    <option value="<?php echo htmlspecialchars($dept['name']); ?>">
                                        <?php echo htmlspecialchars($dept['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="society" class="form-label">Society</label>
                            <select class="form-select" id="society" name="society">
                                <option value="">Select Society</option>
                                <?php while($soc = mysqli_fetch_assoc($societies)): ?>
                                    <option value="<?php echo htmlspecialchars($soc['name']); ?>">
                                        <?php echo htmlspecialchars($soc['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Event Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="manage-events.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add Event</button>
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