<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /Project/pages/auth/login.php');
    exit();
}

// Check if event ID is provided
if (!isset($_GET['id'])) {
    header('Location: /Project/index.php');
    exit();
}

$event_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get event details
$sql = "SELECT * FROM events WHERE id = ? AND created_by = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($result);

// If event doesn't exist or user doesn't have permission
if (!$event) {
    header('Location: /Project/index.php');
    exit();
}

// Get all departments and societies for dropdowns
$departments = [];
$societies = [];

$dept_sql = "SELECT name FROM departments ORDER BY name";
$dept_result = mysqli_query($conn, $dept_sql);
while ($row = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $row['name'];
}

$soc_sql = "SELECT name FROM societies ORDER BY name";
$soc_result = mysqli_query($conn, $soc_sql);
while ($row = mysqli_fetch_assoc($soc_result)) {
    $societies[] = $row['name'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $category = $_POST['category'] ?? '';
    $department = $_POST['department'] ?? '';
    $society = $_POST['society'] ?? '';
    
    // Handle image upload
    $image_url = $event['image_url']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/events/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        // Check if file is an image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($event['image_url'] && file_exists('../../' . $event['image_url'])) {
                    unlink('../../' . $event['image_url']);
                }
                $image_url = 'assets/images/events/' . $new_filename;
            }
        }
    }
    
    // Update event in database
    $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, location = ?, 
            category = ?, department = ?, society = ?, image_url = ? WHERE id = ? AND created_by = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssssii", $title, $description, $event_date, $location, 
                          $category, $department, $society, $image_url, $event_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header('Location: /Project/pages/events/event-details.php?id=' . $event_id);
        exit();
    } else {
        $error = "Failed to update event. Please try again.";
    }
}

// Include header
require_once '../../components/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-white">
                    <h4 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Event</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="event_date" class="form-label">Event Date</label>
                                <input type="datetime-local" class="form-control" id="event_date" 
                                       name="event_date" value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($event['location']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="academic" <?php echo ($event['category'] === 'academic') ? 'selected' : ''; ?>>Academic</option>
                                    <option value="cultural" <?php echo ($event['category'] === 'cultural') ? 'selected' : ''; ?>>Cultural</option>
                                    <option value="sports" <?php echo ($event['category'] === 'sports') ? 'selected' : ''; ?>>Sports</option>
                                    <option value="workshop" <?php echo ($event['category'] === 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                                    <option value="other" <?php echo ($event['category'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo htmlspecialchars($dept); ?>" 
                                                <?php echo ($dept === $event['department']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="society" class="form-label">Society</label>
                                <select class="form-select" id="society" name="society">
                                    <option value="">Select Society (Optional)</option>
                                    <?php foreach ($societies as $soc): ?>
                                        <option value="<?php echo htmlspecialchars($soc); ?>"
                                                <?php echo ($soc === $event['society']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($soc); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Event Image</label>
                            <?php if ($event['image_url']): ?>
                                <div class="mb-2">
                                    <img src="/Project/<?php echo htmlspecialchars($event['image_url']); ?>" 
                                         alt="Current event image" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image. Supported formats: JPG, JPEG, PNG, GIF</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Event
                            </button>
                            <a href="/Project/pages/events/event-details.php?id=<?php echo $event_id; ?>" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../components/footer.php';
?> 