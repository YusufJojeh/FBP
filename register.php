<?php
require_once 'includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';
    // Registration logic here, set $error if any error occurs
    // ... existing code ...
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register – DesignHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family:'Poppins', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
    }
    .login-card {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.2);
      position: relative;
      overflow: hidden;
      animation: float 6s ease-in-out infinite;
    }
    .login-card::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.05)" points="0,1000 1000,0 1000,1000"/></svg>');
      animation: rotateBg 20s linear infinite;
    }
    .login-card > * { position: relative; z-index: 1; }
    h2 {
      font-weight: 800;
      text-align: center;
      margin-bottom: 30px;
      animation: fadeInUp 1s ease both;
    }
    .form-label { color: rgba(255,255,255,0.9); font-weight:500; }
    .form-control {
      background: rgba(255,255,255,0.2);
      border: none;
      color: #fff;
      padding: 12px 15px;
      border-radius: 50px;
      transition: background 0.3s ease;
    }
    .form-control:focus {
      background: rgba(255,255,255,0.3);
      outline: none;
      box-shadow: none;
    }
    .btn-login {
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
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(255,107,107,0.4);
    }
    .register-link {
      display: block;
      text-align: center;
      margin-top: 15px;
      color: rgba(255,255,255,0.8);
      transition: color 0.3s ease;
    }
    .register-link:hover {
      color: #ffd700;
      text-decoration: none;
    }
    .alert {
      background: rgba(255,0,0,0.2);
      border: none;
      color: #ffe5e5;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0);     }
    }
    @keyframes float {
      0%,100% { transform: translateY(0px); }
      50%     { transform: translateY(-15px); }
    }
    @keyframes rotateBg {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }
    @media (max-width: 576px) {
      .login-card { padding: 30px 20px; }
    }
  </style>
</head>
<body>
  <div class="login-card">
    <h2>Create Account</h2>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php" novalidate>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Register as</label>
        <select class="form-select" id="role" name="role" required>
          <option value="client">Client</option>
          <option value="vendor">Vendor</option>
        </select>
      </div>
      <button type="submit" class="btn-login">Register</button>
    </form>
    <a href="login.php" class="register-link">
      <i class="fas fa-sign-in-alt me-1"></i> Already have an account? Login
    </a>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 