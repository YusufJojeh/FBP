<?php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';

function get_count($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($result);
    return $row ? $row[0] : 0;
}
$total_users = get_count($conn, "SELECT COUNT(*) FROM users");
$total_vendors = get_count($conn, "SELECT COUNT(*) FROM users WHERE role = 'vendor'");
$total_services = get_count($conn, "SELECT COUNT(*) FROM services");
$total_bookings = get_count($conn, "SELECT COUNT(*) FROM bookings");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard – DesignHub</title>
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

    .admin-hero {
      padding:5rem 1rem 3rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .admin-hero h1 {
      font-size:2.5rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .admin-hero p {font-size:1.1rem;opacity:.85;}

    .stats-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
      gap:1.7rem;
      padding:2.5rem 1rem 3rem;
      max-width:1200px;margin:0 auto;
    }
    .stat-card {
      background:rgba(255,255,255,0.13);
      backdrop-filter:blur(12px);
      border-radius:22px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      transition:transform .3s,box-shadow .3s;
      padding:2rem 1.5rem;
      display:flex;flex-direction:column;align-items:center;
      animation:float 7s ease-in-out infinite;
      min-height:190px;
      position:relative;
      overflow:hidden;
    }
    .stat-card:hover {
      transform:translateY(-8px) scale(1.03);
      box-shadow:0 30px 80px rgba(0,0,0,0.32);
    }
    .stat-card .stat-icon {
      font-size:2.9rem;
      margin-bottom:.95rem;
      color:#ffd700;
      filter:drop-shadow(0 4px 12px #ffec8b40);
      transition: transform .4s;
      animation: bounceIn 2.3s cubic-bezier(.42,.15,.38,.96) infinite;
    }
    .stat-card:hover .stat-icon {
      transform: scale(1.12) rotate(-5deg);
    }
    .stat-card h3 {
      font-size:2.3rem;
      font-weight:800;
      margin-bottom:.3rem;
      letter-spacing:.5px;
      color:#fff;
    }
    .stat-card p {
      font-size:1.13rem;
      opacity:.87;
      letter-spacing:.01rem;
      color:#ffd700;
      font-weight:600;
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    @keyframes bounceIn {
      0%,100%{transform:translateY(0)}
      15%{transform:translateY(-7px) scale(1.08)}
      45%{transform:translateY(-2px) scale(1.03)}
      65%{transform:translateY(-4px) scale(1.09)}
      80%{transform:translateY(-2px)}
    }
    .footer-spacer{height:2.2rem;}
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
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_services.php">Manage Services</a></li>
          <li class="nav-item"><a class="nav-link" href="site_settings.php">Site Settings</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="awards.php">Awarding</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Admin Hero -->
  <section class="admin-hero">
    <h1>Admin Dashboard</h1>
    <p>Quick overview of your platform statistics</p>
  </section>

  <!-- Stats Grid -->
  <section class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-users"></i></div>
      <h3><?= $total_users ?></h3>
      <p>Total Users</p>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
      <h3><?= $total_vendors ?></h3>
      <p>Total Vendors</p>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
      <h3><?= $total_services ?></h3>
      <p>Total Services</p>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
      <h3><?= $total_bookings ?></h3>
      <p>Total Bookings</p>
    </div>
  </section>

  <div class="footer-spacer"></div>

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
