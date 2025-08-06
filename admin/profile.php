<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$admin_id = $_SESSION['user_id'];
$msg = '';
$success = false;

// Fetch admin profile
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ? AND role = 'admin'");
mysqli_stmt_bind_param($stmt, 'i', $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $error = false;
    
    // Verify current password if trying to change password
    if ($new_password) {
        if ($current_password !== $profile['password']) {
            $msg = 'Current password is incorrect';
            $error = true;
        } elseif ($new_password !== $confirm_password) {
            $msg = 'New passwords do not match';
            $error = true;
        }
    }
    
    // Profile image upload
    $profile_image = $profile['profile_image'] ?? '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('profile_') . '.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                if ($profile['profile_image'] && file_exists('../uploads/' . $profile['profile_image'])) {
                    unlink('../uploads/' . $profile['profile_image']);
                }
                $profile_image = $new_filename;
            }
        }
    }
    
    if (!$error) {
        mysqli_begin_transaction($conn);
        try {
            // Only update fields that have changed
            $update_fields = [];
            $update_values = [];
            $update_types = '';
            
            if ($username !== $profile['username']) {
                $update_fields[] = 'username = ?';
                $update_values[] = $username;
                $update_types .= 's';
            }
            
            if ($email !== $profile['email']) {
                $update_fields[] = 'email = ?';
                $update_values[] = $email;
                $update_types .= 's';
            }
            
            if ($new_password) {
                $update_fields[] = 'password = ?';
                $update_values[] = $new_password;
                $update_types .= 's';
            }
            
            if ($profile_image !== ($profile['profile_image'] ?? '')) {
                $update_fields[] = 'profile_image = ?';
                $update_values[] = $profile_image;
                $update_types .= 's';
            }
            
            if (!empty($update_fields)) {
                $update_values[] = $admin_id;
                $update_types .= 'i';
                
                $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ? AND role = 'admin'";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, $update_types, ...$update_values);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            
            mysqli_commit($conn);
            $msg = 'Profile updated successfully!';
            $success = true;
            
            // Update profile data for display
            $profile['username'] = $username;
            $profile['email'] = $email;
            $profile['profile_image'] = $profile_image;
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $msg = 'Error updating profile. Please try again.';
        }
    }
}

// Get last login time (you would need to implement login time tracking)
$last_login = "2024-01-21 15:30:00"; // Example static date, implement actual tracking
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Profile – DesignHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif;
            background: linear-gradient(135deg,#667eea,#764ba2);
            color:#fff;
            min-height:100vh;
            overflow-x:hidden;
        }
        .navbar {
            background:rgba(255,255,255,0.1)!important;
            backdrop-filter:blur(10px);
            transition:background .3s,box-shadow .3s;
        }
        .navbar.scrolled {
            background:rgba(255,255,255,0.95)!important;
            box-shadow:0 2px 20px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link { color:#fff!important; font-weight:500; }
        .nav-link.active, .nav-link:hover { color:#ffd700!important; }
        .nav-link { margin:0 .5rem; position:relative; }
        .nav-link::after {
            content:''; position:absolute; bottom:-4px; left:50%;
            width:0; height:2px; background:#ffd700;
            transform:translateX(-50%); transition:width .3s;
        }
        .nav-link:hover::after, .nav-link.active::after { width:100%; }

        .admin-hero {
            padding:4rem 1rem 2rem;
            text-align:center;
            animation:fadeInUp 1s both;
        }
        .admin-hero h1 { 
            font-size:2.2rem;
            font-weight:800;
            text-shadow:0 2px 8px rgba(0,0,0,0.3);
        }
        .admin-hero p { font-size:1.08rem; opacity:.85; }

        .profile-container {
            max-width:900px;
            margin:2rem auto;
            padding:0 1rem;
        }

        .profile-grid {
            display:grid;
            grid-template-columns: 300px 1fr;
            gap:2rem;
        }

        @media (max-width:768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        .profile-sidebar {
            background:rgba(255,255,255,0.12);
            backdrop-filter:blur(10px);
            border-radius:20px;
            padding:2rem;
            text-align:center;
            animation:float 7s ease-in-out infinite;
        }

        .profile-avatar {
            width:120px;
            height:120px;
            margin:0 auto 1.5rem;
            border-radius:50%;
            background:#ffd700;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:3rem;
            color:#764ba2;
            box-shadow:0 8px 32px rgba(0,0,0,0.2);
            position:relative; /* Added for positioning the form */
        }

        .profile-image {
            width:100%;
            height:100%;
            border-radius:50%;
            object-fit:cover;
            border:3px solid #ffd700;
            box-shadow:0 5px 15px rgba(0,0,0,.2);
        }

        .profile-info h3 {
            font-size:1.5rem;
            font-weight:700;
            margin-bottom:.5rem;
        }

        .profile-info p {
            font-size:.9rem;
            opacity:.8;
            margin-bottom:.3rem;
        }

        .profile-stats {
            margin-top:1.5rem;
            padding-top:1.5rem;
            border-top:1px solid rgba(255,255,255,0.1);
        }

        .stat-item {
            margin-bottom:1rem;
            text-align:left;
        }

        .stat-item i {
            width:30px;
            color:#ffd700;
        }

        .profile-main {
            background:rgba(255,255,255,0.12);
            backdrop-filter:blur(10px);
            border-radius:20px;
            padding:2rem;
            animation:float 7s ease-in-out infinite;
        }

        .form-section {
            margin-bottom:2rem;
        }

        .form-section h4 {
            color:#ffd700;
            font-size:1.2rem;
            font-weight:600;
            margin-bottom:1.5rem;
        }

        .form-floating label {
            color:#ffd700;
        }

        .form-control {
            background:rgba(255,255,255,0.15);
            border:1px solid rgba(255,255,255,0.2);
            color:#fff;
        }

        .form-control:focus {
            background:rgba(255,255,255,0.2);
            border-color:#ffd700;
            color:#fff;
            box-shadow:0 0 0 0.25rem rgba(255,215,0,0.25);
        }

        .btn-update {
            background:linear-gradient(45deg,#ffd700,#ff537e);
            border:none;
            padding:12px 30px;
            font-weight:600;
            border-radius:50px;
            box-shadow:0 5px 15px rgba(255,83,126,0.3);
            transition:all .3s;
        }

        .btn-update:hover {
            transform:translateY(-3px);
            box-shadow:0 8px 25px rgba(255,83,126,0.4);
            background:linear-gradient(45deg,#ff537e,#ffd700);
        }

        .alert-profile {
            position:fixed;
            top:20px;
            right:20px;
            padding:1rem 2rem;
            border-radius:50px;
            background:rgba(40,167,69,0.95);
            color:#fff;
            box-shadow:0 5px 15px rgba(0,0,0,0.2);
            display:none;
            animation:slideIn .5s ease;
        }

        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(30px); }
            to { opacity:1; transform:translateY(0); }
        }

        @keyframes float {
            0%,100% { transform:translateY(0); }
            50% { transform:translateY(-10px); }
        }

        @keyframes slideIn {
            from { transform:translateX(100%); opacity:0; }
            to { transform:translateX(0); opacity:1; }
        }
        .coming-soon-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-10deg);
            background: rgba(255,215,0,0.97);
            color: #764ba2;
            font-size: 2.7rem;
            font-weight: 900;
            padding: 1.2rem 3.5rem;
            border-radius: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            z-index: 100;
            opacity: 0.93;
            letter-spacing: 2px;
            text-shadow: 0 2px 8px #fff8;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top py-2">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php"><i class="fas fa-palette me-2"></i>DesignHub</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_services.php">Manage Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="site_settings.php">Site Settings</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="awards.php">Awarding</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="admin-hero">
        <h1>Admin Profile</h1>
        <p>Manage your account settings and security</p>
    </section>

    <div class="profile-container">
        <div class="profile-grid">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php if (!empty($profile['profile_image']) && file_exists('../uploads/' . $profile['profile_image'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($profile['profile_image']) ?>" alt="Profile" class="profile-image" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #ffd700;box-shadow:0 5px 15px rgba(0,0,0,.2);">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/120x120" alt="Profile" class="profile-image" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #ffd700;box-shadow:0 5px 15px rgba(0,0,0,.2);">
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" id="profile_image_form">
                        <label class="profile-image-upload" title="Upload new image" style="position:absolute;bottom:0;right:0;background:#ffd700;border-radius:50%;width:35px;height:35px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                            <i class="fas fa-camera"></i>
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display:none" onchange="document.getElementById('profile_image_form').submit();">
                        </label>
                    </form>
                </div>
                <div class="profile-info">
                    <h3><?= htmlspecialchars($profile['username']) ?></h3>
                    <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($profile['email']) ?></p>
                    <p><i class="fas fa-shield-alt me-2"></i>Administrator</p>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <i class="fas fa-clock me-2"></i>
                        Last Login:<br>
                        <?= date('M j, Y g:i A', strtotime($last_login)) ?>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-user-shield me-2"></i>
                        Account Status:<br>
                        <span class="text-success">Active</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Member Since:<br>
                        <?= date('M j, Y', strtotime($profile['created_at'])) ?>
                    </div>
                </div>
            </div>

            <!-- Profile Main Content -->
            <div class="profile-main">
                <form method="POST" class="needs-validation" novalidate>
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h4><i class="fas fa-user me-2"></i>Basic Information</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?= htmlspecialchars($profile['username']) ?>" required>
                                    <label for="username">Username</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($profile['email']) ?>" required>
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password -->
                    <div class="form-section">
                        <h4><i class="fas fa-lock me-2"></i>Change Password</h4>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Current Password">
                                    <label for="current_password">Current Password</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="New Password">
                                    <label for="new_password">New Password</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
                                    <label for="confirm_password">Confirm New Password</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-update">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($msg): ?>
        <script>
            window.addEventListener('DOMContentLoaded', function(){
                showToast('<?= htmlspecialchars($msg) ?>');
            });
        </script>
    <?php endif; ?>
    <div id="toast" class="toast-notify"></div>
    <style>
        .toast-notify {
            position: fixed;
            bottom: 32px;
            right: 32px;
            background: rgba(44,44,56,.98);
            color: #ffd700;
            font-weight: 600;
            font-size: 1.08rem;
            border-radius: 13px;
            box-shadow: 0 3px 12px #2228;
            padding: 15px 28px;
            z-index: 1200;
            display: none;
            align-items: center;
            animation: fadeInUp .75s;
        }
        @media (max-width:600px) {
            .toast-notify { left:10px; right:10px; bottom:18px; font-size:.98rem; }
        }
        @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    </style>
    <script>
        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.style.display = 'flex';
            setTimeout(()=>toast.style.display='none', 3000);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        const nav = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });

        // Password validation
        document.getElementById('new_password').addEventListener('input', function() {
            const confirm = document.getElementById('confirm_password');
            if (confirm.value) {
                confirm.setCustomValidity(this.value !== confirm.value ? 'Passwords do not match.' : '');
            }
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPass = document.getElementById('new_password');
            this.setCustomValidity(this.value !== newPass.value ? 'Passwords do not match.' : '');
        });
    </script>
</body>
</html> 