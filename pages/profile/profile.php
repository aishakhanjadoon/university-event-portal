<?php
require_once '../../config/database.php';
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: /Project/pages/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle profile update
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $notify_new_events = isset($_POST['notify_new_events']) ? 1 : 0;
        $notify_registration = isset($_POST['notify_registration']) ? 1 : 0;
        $notify_updates = isset($_POST['notify_updates']) ? 1 : 0;
        
        // Validate input
        if(empty($full_name) || empty($email)) {
            $error = "Please fill in all required fields.";
        } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            // Handle profile picture upload
            $profile_picture = $user['profile_picture']; // Keep existing picture by default
            if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['profile_picture']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if(in_array(strtolower($filetype), $allowed)) {
                    $upload_dir = '../../uploads/profiles/';
                    
                  //naya path pic k lyay
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $filetype;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if(move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        // pichli delete
                        if($user['profile_picture'] && file_exists('../../' . ltrim($user['profile_picture'], '/'))) {
                            unlink('../../' . ltrim($user['profile_picture'], '/'));
                        }
                        $profile_picture = '/Project/uploads/profiles/' . $new_filename;
                    }
                }
            }
            
            // Update user 
            $sql = "UPDATE users SET full_name = ?, email = ?, profile_picture = ?, 
                    notify_new_events = ?, notify_registration = ?, notify_updates = ? 
                    WHERE id = ?";
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssiiii", $full_name, $email, $profile_picture, 
                                     $notify_new_events, $notify_registration, $notify_updates, $user_id);
                if(mysqli_stmt_execute($stmt)) {
                    $success = "Profile updated successfully.";
                    $_SESSION['user_full_name'] = $full_name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_profile_picture'] = $profile_picture;
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    } elseif(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // checck input
        if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "Please fill in all password fields.";
        } elseif($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif(strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } else {
            // password
            $sql = "SELECT password FROM users WHERE id = ?";
            if($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if($row = mysqli_fetch_assoc($result)) {
                    if(password_verify($current_password, $row['password'])) {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET password = ? WHERE id = ?";
                        if($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
                            if(mysqli_stmt_execute($stmt)) {
                                $success = "Password changed successfully.";
                            } else {
                                $error = "Failed to update password. Please try again.";
                            }
                            mysqli_stmt_close($stmt);
                        }
                    } else {
                        $error = "Current password is incorrect.";
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get user information
$sql = "SELECT * FROM users WHERE id = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Get user's event registrations
$sql = "SELECT e.*, er.registration_date, 
        (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as total_registrations
        FROM events e 
        JOIN event_registrations er ON e.id = er.event_id 
        WHERE er.user_id = ? 
        ORDER BY e.event_date DESC";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $registrations = [];
    while($row = mysqli_fetch_assoc($result)) {
        $registrations[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Get user's created events (if representative)
$created_events = [];
if($user['role'] === 'representative') {
    $sql = "SELECT e.*, 
            (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) as registration_count
            FROM events e 
            WHERE e.created_by = ? 
            ORDER BY e.event_date DESC";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)) {
            $created_events[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - University Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/Project/assets/css/style.css">
    <style>
        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
        }
        .profile-picture-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        .profile-picture-upload {
            position: relative;
            display: inline-block;
        }
        .profile-picture-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
      
        .event-link {
            color: var(--text-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .event-link:hover {
            color: var(--primary-color);
            text-decoration: none;
        }
      
        .profile-picture.bg-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #6c757d !important;
            color: white;
            font-size: 3rem;
        }
    </style>
</head>
<body>
    <?php include '../../components/header.php'; ?>

    <div class="container py-4">
        <div class="row">
           
            <div class="col-md-4">
                <div class="card shadow-lg mb-4">
                    <div class="card-body">
                        <div class="profile-picture-container">
                            <div class="profile-picture-upload">
                                <?php if($user['profile_picture']): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                         alt="Profile Picture" class="profile-picture">
                                <?php else: ?>
                                    <div class="profile-picture d-flex align-items-center justify-content-center bg-secondary text-white">
                                        <i class="fas fa-user fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="profile_picture" form="profile-form" accept="image/*">
                                <div class="mt-2">
                                    <small class="text-muted">Click to change profile picture</small>
                                </div>
                            </div>
                        </div>

                        <h2 class="card-title mb-4">Profile Information</h2>
                        
                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if(!empty($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" id="profile-form" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role" 
                                       value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" disabled>
                            </div>

                            <?php if($user['role'] === 'representative'): ?>
                                <div class="mb-3">
                                    <label for="society" class="form-label">Society</label>
                                    <input type="text" class="form-control" id="society" 
                                           value="<?php echo htmlspecialchars($user['society']); ?>" disabled>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" class="form-control" id="department" 
                                       value="<?php echo htmlspecialchars($user['department']); ?>" disabled>
                            </div>

                            <div class="mb-4">
                                <h5>Email Notifications</h5>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="notify_new_events" 
                                           name="notify_new_events" <?php echo $user['notify_new_events'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notify_new_events">
                                        New events in my department/society
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="notify_registration" 
                                           name="notify_registration" <?php echo $user['notify_registration'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notify_registration">
                                        Event registration confirmations
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notify_updates" 
                                           name="notify_updates" <?php echo $user['notify_updates'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="notify_updates">
                                        Event updates and reminders
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Profile
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h5 class="mb-3">Change Password</h5>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                                <div class="invalid-feedback">Please enter your current password.</div>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required minlength="8">
                                <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="change_password" class="btn btn-outline-primary">
                                    <i class="bi bi-key"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Event Registrations -->
            <div class="col-md-8">
                <div class="card shadow-lg mb-4">
                    <div class="card-body">
                        <h2 class="card-title mb-4">My Event Registrations</h2>
                        
                        <?php if(empty($registrations)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> You haven't registered for any events yet.
                                <a href="/Project/pages/events/society-events.php" class="alert-link">Browse events</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Event</th>
                                            <th>Date</th>
                                            <th>Category</th>
                                            <th>Registered On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($registrations as $registration): ?>
                                            <tr>
                                                <td>
                                                    <a href="/Project/pages/events/event-details.php?id=<?php echo $registration['id']; ?>" class="event-link">
                                                        <?php echo htmlspecialchars($registration['title']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($registration['event_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo ucfirst(htmlspecialchars($registration['category'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($registration['registration_date'])); ?></td>
                                                <td>
                                                    <a href="/Project/pages/events/event-details.php?id=<?php echo $registration['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($user['role'] === 'representative'): ?>
                    <!-- Created Events -->
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="card-title mb-0">My Created Events</h2>
                                <a href="/Project/pages/events/add-event.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Create Event
                                </a>
                            </div>
                            
                            <?php if(empty($created_events)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> You haven't created any events yet.
                                    <a href="/Project/pages/events/add-event.php" class="alert-link">Create your first event</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Event</th>
                                                <th>Date</th>
                                                <th>Category</th>
                                                <th>Registrations</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($created_events as $event): ?>
                                                <tr>
                                                    <td>
                                                        <a href="/Project/pages/events/event-details.php?id=<?php echo $event['id']; ?>" class="event-link">
                                                            <?php echo htmlspecialchars($event['title']); ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-primary">
                                                            <?php echo htmlspecialchars($event['category']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $event['registration_count']; ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="/Project/pages/events/edit-event.php?id=<?php echo $event['id']; ?>" 
                                                               class="btn btn-sm btn-outline-secondary">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </a>
                                                            <a href="/Project/pages/events/delete-event.php?id=<?php echo $event['id']; ?>" 
                                                               class="btn btn-sm btn-outline-danger"
                                                               onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/Project/assets/js/main.js"></script>
    <script>
    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if(!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Password confirmation validation
    document.querySelector('form[name="change_password"]').addEventListener('submit', function(event) {
        var newPassword = document.getElementById('new_password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
        
        if(newPassword !== confirmPassword) {
            event.preventDefault();
            document.getElementById('confirm_password').setCustomValidity('Passwords do not match');
        } else {
            document.getElementById('confirm_password').setCustomValidity('');
        }
    });

    // Profile picture preview
    document.querySelector('input[type="file"]').addEventListener('change', function(event) {
        if(event.target.files && event.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('.profile-picture').src = e.target.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    });
    </script>
</body>
</html> 