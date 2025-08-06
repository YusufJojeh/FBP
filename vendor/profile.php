<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$vendor_id = $_SESSION['user_id'];
$msg = '';
$success = false;

// Fetch vendor profile
$stmt = mysqli_prepare($conn, "SELECT u.*, v.display_name, v.bio, v.profile_image FROM users u LEFT JOIN vendors v ON u.id = v.user_id WHERE u.id = ?");
mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Check if vendor record exists, if not create it
if (!isset($profile['display_name'])) {
    mysqli_query($conn, "INSERT INTO vendors (user_id) VALUES ($vendor_id)");
    $profile['display_name'] = '';
    $profile['bio'] = '';
    $profile['profile_image'] = '';
}

// Ensure $profile keys exist to avoid undefined array key warnings
$profile['username'] = $profile['username'] ?? '';
$profile['email'] = $profile['email'] ?? '';
$profile['display_name'] = $profile['display_name'] ?? '';
$profile['bio'] = $profile['bio'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $display_name = trim($_POST['display_name']);
    $bio = trim($_POST['bio']);
    $password = $_POST['password'] ?? '';

    // Handle profile image upload
    $profile_image = $profile['profile_image']; // Keep existing image by default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif','svg'];
        $filename = $_FILES['profile_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('profile_') . '.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($profile['profile_image'] && file_exists('../uploads/' . $profile['profile_image'])) {
                    unlink('../uploads/' . $profile['profile_image']);
                }
                $profile_image = $new_filename;
            }
        }
    }

    // Start transaction
    mysqli_begin_transaction($conn);
    try {
        // Only update users table fields that have changed
        $user_update_fields = [];
        $user_update_values = [];
        $user_update_types = '';
        
        if ($username !== $profile['username']) {
            $user_update_fields[] = 'username = ?';
            $user_update_values[] = $username;
            $user_update_types .= 's';
        }
        
        if ($email !== $profile['email']) {
            $user_update_fields[] = 'email = ?';
            $user_update_values[] = $email;
            $user_update_types .= 's';
        }
        
        if ($password) {
            $user_update_fields[] = 'password = ?';
            $user_update_values[] = $password;
            $user_update_types .= 's';
        }
        
        if (!empty($user_update_fields)) {
            $user_update_values[] = $vendor_id;
            $user_update_types .= 'i';
            
            $sql = "UPDATE users SET " . implode(', ', $user_update_fields) . " WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, $user_update_types, ...$user_update_values);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Only update vendors table fields that have changed
        $vendor_update_fields = [];
        $vendor_update_values = [];
        $vendor_update_types = '';
        
        if ($display_name !== $profile['display_name']) {
            $vendor_update_fields[] = 'display_name = ?';
            $vendor_update_values[] = $display_name;
            $vendor_update_types .= 's';
        }
        
        if ($bio !== $profile['bio']) {
            $vendor_update_fields[] = 'bio = ?';
            $vendor_update_values[] = $bio;
            $vendor_update_types .= 's';
        }
        
        if ($profile_image !== ($profile['profile_image'] ?? '')) {
            $vendor_update_fields[] = 'profile_image = ?';
            $vendor_update_values[] = $profile_image;
            $vendor_update_types .= 's';
        }
        
        if (!empty($vendor_update_fields)) {
            $vendor_update_values[] = $vendor_id;
            $vendor_update_types .= 'i';
            
            $sql = "UPDATE vendors SET " . implode(', ', $vendor_update_fields) . " WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, $vendor_update_types, ...$vendor_update_values);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($conn);
        $msg = 'Profile updated successfully!';
        $success = true;

        // Update profile data for display
        $profile['username'] = $username;
        $profile['email'] = $email;
        $profile['display_name'] = $display_name;
        $profile['bio'] = $bio;
        $profile['profile_image'] = $profile_image;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = 'Error updating profile. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vendor Profile – DesignHub</title>
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

        .profile-container {
            max-width:1200px; /* Increased max-width for grid layout */
            margin:4.5rem auto 2rem;
            padding:2.5rem;
            background:rgba(255,255,255,0.11);
            border-radius:22px;
            box-shadow:0 16px 40px rgba(0,0,0,.23);
            backdrop-filter: blur(12px);
            animation: fadeInUp 1s;
            display:flex; /* Use flexbox for grid layout */
            gap:2rem; /* Space between sidebar and main content */
        }
        .profile-grid {
            display:grid;
            grid-template-columns: 1fr 2fr; /* Sidebar (1/3) and Main content (2/3) */
            gap:2rem;
        }
        .profile-sidebar {
            background:rgba(255,255,255,0.08);
            border-radius:15px;
            padding:2rem;
            display:flex;
            flex-direction:column;
            align-items:center;
            text-align:center;
        }
        .profile-avatar {
            position:relative;
            width:120px;
            height:120px;
            margin-bottom:1.5rem;
        }
        .profile-image {
            width:100%;
            height:100%;
            border-radius:50%;
            object-fit:cover;
            border:3px solid #ffd700;
            box-shadow:0 5px 15px rgba(0,0,0,.2);
        }
        .profile-image-upload {
            position:absolute;
            bottom:0;
            right:0;
            background:#ffd700;
            border-radius:50%;
            width:35px;
            height:35px;
            display:flex;
            align-items:center;
            justify-content:center;
            cursor:pointer;
            transition:transform .3s;
        }
        .profile-image-upload:hover {
            transform:scale(1.1);
        }
        .profile-image-upload input {
            display:none;
        }
        .profile-info h3 {
            font-weight:800;
            font-size:1.8rem;
            margin-bottom:.5rem;
        }
        .profile-info p {
            color:#ffd700;
            font-size:1.1rem;
            opacity:.9;
            margin-bottom:.5rem;
        }
        .profile-info p i {
            margin-right:5px;
        }
        .profile-stats {
            display:flex;
            justify-content:space-around;
            width:100%;
            margin-top:1.5rem;
            padding:1rem 0;
            border-top:1px solid rgba(255,255,255,0.2);
            border-bottom:1px solid rgba(255,255,255,0.2);
        }
        .stat-item {
            text-align:center;
        }
        .stat-item i {
            font-size:1.5rem;
            margin-bottom:.5rem;
            color:#ffd700;
        }
        .stat-item br {
            display:none; /* Remove line break for smaller screens */
        }
        .form-section {
            background:rgba(255,255,255,0.08);
            border-radius:15px;
            padding:2rem;
            margin-bottom:2rem;
        }
        .form-section h4 {
            font-weight:600;
            font-size:1.4rem;
            margin-bottom:1.5rem;
            color:#ffd700;
        }
        .form-section h4 i {
            margin-right:10px;
            color:#ffd700;
        }
        .form-floating label {
            color: #ffd700;
            font-weight:500;
        }
        .form-control, .form-control:focus {
            background:rgba(255,255,255,0.15);
            border:1px solid rgba(255,255,255,0.2);
            color:#fff;
            font-size:1rem;
        }
        .form-control:focus {
            background:rgba(255,255,255,0.2);
            border-color:#ffd700;
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
            left:50%;
            transform:translateX(-50%);
            background:rgba(40,167,69,0.95);
            color:#fff;
            padding:15px 30px;
            border-radius:50px;
            box-shadow:0 5px 15px rgba(0,0,0,.2);
            display:none;
            animation:fadeInDown .5s;
        }
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(30px); }
            to { opacity:1; transform:translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity:0; transform:translate(-50%, -20px); }
            to { opacity:1; transform:translate(-50%, 0); }
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
                    <li class="nav-item"><a class="nav-link" href="services.php">My Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_service.php">Add Service</a></li>
                    <li class="nav-item"><a class="nav-link" href="update_order.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="chats.php">Chats</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-grid">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar" style="position:relative;">
                <div class="profile-avatar" style="position:relative;">
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
                    <h3><?= htmlspecialchars($profile['display_name'] ?: $profile['username']) ?></h3>
                    <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($profile['email']) ?></p>
                    <p><i class="fas fa-user-tie me-2"></i>Vendor</p>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <i class="fas fa-clock me-2"></i>
                        Last Login:<br>
                        <?= $profile['last_login'] ? date('M j, Y g:i A', strtotime($profile['last_login'])) : '-' ?>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-user-check me-2"></i>
                        Account Status:<br>
                        <span class="<?= $profile['status'] === 'active' ? 'text-success' : ($profile['status'] === 'suspended' ? 'text-danger' : 'text-warning') ?>">
                            <?= ucfirst($profile['status']) ?>
                        </span>
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
                <form method="POST" enctype="multipart/form-data" id="profile_form">
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
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="display_name" name="display_name" placeholder="Display Name" value="<?= htmlspecialchars($profile['display_name']) ?>" required>
                                    <label for="display_name">Display Name (shown to clients)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="bio" name="bio" placeholder="Bio" style="height:120px"><?= htmlspecialchars($profile['bio']) ?></textarea>
                                    <label for="bio">Bio (tell clients about yourself)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h4><i class="fas fa-lock me-2"></i>Change Password</h4>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="New Password">
                                    <label for="password">New Password (leave blank to keep current)</label>
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

        // Profile image upload
        // document.getElementById('profile_image_input').addEventListener('change', function(e) {
        //     if (this.files && this.files[0]) {
        //         const file = this.files[0];
        //         const actualFileInput = document.getElementById('profile_image');
                
        //         // Update hidden file input
        //         const dataTransfer = new DataTransfer();
        //         dataTransfer.items.add(file);
        //         actualFileInput.files = dataTransfer.files;
                
        //         // Preview image
        //         const reader = new FileReader();
        //         reader.onload = function(e) {
        //             document.querySelector('.profile-image').src = e.target.result;
        //         };
        //         reader.readAsDataURL(file);
                
        //         // Auto-submit form
        //         document.getElementById('profile_form').submit();
        //     }
        // });

        // Success message
        <?php if ($success): ?>
        // showToast('Profile updated successfully!'); // This is now handled by the PHP script
        <?php endif; ?>
    </script>
</body>
</html> 