<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Site Settings – DesignHub</title>
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
      padding:4rem 1rem 2rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .admin-hero h1 {font-size:2.2rem;font-weight:800;text-shadow:0 2px 8px rgba(0,0,0,0.3);}
    .admin-hero p {font-size:1.08rem;opacity:.85;}
    .settings-card {
      background:rgba(255,255,255,0.12);
      backdrop-filter:blur(10px);
      border-radius:20px;
      box-shadow:0 18px 56px rgba(0,0,0,0.18);
      padding:2.5rem 2rem 2rem 2rem;
      max-width:500px;
      margin:2.5rem auto 0;
      animation:float 7s ease-in-out infinite;
    }
    .settings-card h3 {
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
    .btn-settings {
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
    .btn-settings:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(255,107,107,0.4);
    }
    .alert-info {
      background: rgba(23,162,184,0.2);
      border: none;
      color: #17a2b8;
      border-radius: 10px;
      text-align:center;
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
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
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
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

  <section class="admin-hero">
    <h1>Site Settings</h1>
    <p>Platform configuration and maintenance</p>
  </section>

  <div class="settings-card shadow-lg">
    <h3><i class="fas fa-cogs me-2"></i>Settings</h3>
    <form>
      <div class="mb-3">
        <label class="form-label">Site Name</label>
        <input type="text" class="form-control" value="DesignHub" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Maintenance Mode</label>
        <select class="form-select" disabled>
          <option>Off</option>
          <option>On</option>
        </select>
      </div>
      <button type="submit" class="btn btn-settings" disabled>Save Settings</button>
    </form>
    <div class="alert alert-info mt-3">Settings management coming soon.</div>
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