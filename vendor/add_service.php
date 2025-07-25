<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$vendor_user_id = $_SESSION['user_id'];
// Get vendor's id from vendors table
$stmt = mysqli_prepare($conn, "SELECT id FROM vendors WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $vendor_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vendor = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!$vendor) {
    // Create vendor profile if not exists
    mysqli_query($conn, "INSERT INTO vendors (user_id) VALUES ($vendor_user_id)");
    $vendor['id'] = mysqli_insert_id($conn);
}
$vendor_id = $vendor['id'];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $stmt = mysqli_prepare($conn, "INSERT INTO services (vendor_id, title, description, price, category) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issds', $vendor_id, $title, $desc, $price, $category);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $msg = 'Service added!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Service – DesignHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * {margin:0;padding:0;box-sizing:border-box;}
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
    .navbar-brand, .nav-link {color:#fff!important;font-weight:500;}
    .nav-link.active, .nav-link:hover {color:#ffd700!important;}
    .nav-link {margin:0 .5rem;position:relative;}
    .nav-link::after {
      content:'';position:absolute;bottom:-4px;left:50%;
      width:0;height:2px;background:#ffd700;
      transform:translateX(-50%);transition:width .3s;
    }
    .nav-link:hover::after, .nav-link.active::after {width:100%;}
    .add-hero {
      padding:5rem 1rem 2.5rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .add-hero h1 {
      font-size:2.2rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .add-hero p {font-size:1.1rem;opacity:.85;}
    .add-card {
      background:rgba(255,255,255,0.13);
      backdrop-filter:blur(12px);
      border-radius:20px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      padding:2.5rem 2rem 2rem 2rem;
      max-width:500px;
      margin:2.5rem auto 0;
      animation:float 7s ease-in-out infinite;
    }
    .add-card h3 {
      font-size:1.5rem;
      font-weight:700;
      margin-bottom:1.2rem;
      color:#ffd700;
    }
    .form-label { color: #ffd700; font-weight:500; }
    .form-control, .form-select {
      background:rgba(255,255,255,0.2);
      border: none;
      color: #fff;
      padding: 12px 15px;
      border-radius: 50px;
      transition: background 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
      background: rgba(255,255,255,0.3);
      outline: none;
      box-shadow: none;
    }
    .btn-add {
      width: 100%;
      padding: 12px;
      font-weight: 600;
      border-radius: 50px;
      background: linear-gradient(45deg, #ff6b6b, #ffd93d);
      border: none;
      color: #fff;
      margin-top: 20px;
      box-shadow: 0 10px 30px rgba(255,107,107,0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-add:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(255,107,107,0.4);
    }
    .alert-success {
      background:rgba(40,167,69,0.18);
      color:#28a745;
      border:none;
      border-radius:12px;
      text-align:center;
      margin-bottom:1.2rem;
      font-weight:600;
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)} }
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top py-2">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php"><i class="fas fa-palette me-2"></i>DesignHub</a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="services.php">My Services</a></li>
          <li class="nav-item"><a class="nav-link active" href="add_service.php">Add Service</a></li>
          <li class="nav-item"><a class="nav-link" href="update_order.php">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="chats.php">Chats</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="add-hero">
    <h1>Add New Service</h1>
    <p>Expand your offerings and reach more clients</p>
  </section>

  <div class="add-card shadow-lg">
    <h3><i class="fas fa-plus-circle me-2"></i>Add Service</h3>
    <?php if ($msg): ?><div class="alert alert-success">Service added!</div><?php endif; ?>
    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" required style="border-radius:18px;min-height:90px;resize:vertical;"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Price</label>
        <input type="number" name="price" class="form-control" required min="0" step="0.01">
      </div>
      <div class="mb-3">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" required>
      </div>
      <button type="submit" class="btn-add"><i class="fas fa-plus me-2"></i>Add Service</button>
    </form>
  </div>

  <div style="height:2rem;"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
  </script>
</body>
</html> 