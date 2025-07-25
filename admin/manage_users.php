<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';

// DELETE USER
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // prevent deleting yourself
    if ($id !== $_SESSION['user_id']) {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: manage_users.php?msg=User+deleted!');
        exit;
    }
}

// CREATE USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = $_POST['password'];
    if ($username && $email && $role && $status && $password) {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $username, $email, $password, $role, $status);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: manage_users.php?msg=User+added!');
        exit;
    }
}

// UPDATE USER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = $_POST['password'];
    if ($password) {
        $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, role=?, status=?, password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssi', $username, $email, $role, $status, $password, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, role=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssi', $username, $email, $role, $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header('Location: manage_users.php?msg=User+updated!');
    exit;
}

// FETCH ALL USERS
$users = [];
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
$roles = ['admin' => 'Admin', 'vendor' => 'Vendor', 'client' => 'Client'];
$statuses = ['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Users – DesignHub Admin</title>
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
    .manage-card {
      background:rgba(255,255,255,0.12);
      backdrop-filter:blur(10px);
      border-radius:20px;
      box-shadow:0 18px 56px rgba(0,0,0,0.18);
      padding:2rem 1rem 1rem 1rem;
      max-width:1200px;
      margin:auto;
      animation:float 7s ease-in-out infinite;
    }
    .manage-card table {
      background:transparent;
      color:#fff;
    }
    .manage-card th, .manage-card td {
      border-color:rgba(255,255,255,0.08);
      vertical-align:middle;
      font-size:1.02rem;
      background:rgba(255,255,255,0.05);
      transition:background .3s;
    }
    .manage-card tr:hover td {
      background:rgba(255,255,255,0.13);
      transition:background .2s;
    }
    .badge-role-admin    {background:#ff537e;color:#fff;}
    .badge-role-vendor   {background:#ffd700;color:#253053;}
    .badge-role-client   {background:#48d9ad;color:#fff;}
    .badge-status-active {background:#48d9ad;color:#fff;}
    .badge-status-inactive {background:#ffc107;color:#253053;}
    .badge-status-suspended {background:#dc3545;color:#fff;}
    .btn-action {
      border-radius:22px;font-size:.97rem;font-weight:600;box-shadow:0 1px 10px #0001;
    }
    .btn-edit {background:linear-gradient(90deg,#ffd700,#ff537e 99%);color:#253053;border:none;}
    .btn-edit:hover {background:linear-gradient(120deg,#ff537e,#ffd700 90%);color:#fff;}
    .btn-del {background:linear-gradient(90deg,#ff537e,#ffd700 99%);color:#fff;border:none;}
    .btn-del:hover {background:linear-gradient(120deg,#ffd700,#ff537e 90%);}
    .btn-adduser {
      background: linear-gradient(90deg,#48d9ad,#764ba2);
      color: #fff;
      font-weight: 700;
      border: none;
      box-shadow: 0 2px 12px #764ba242;
      border-radius: 22px;
      margin-bottom: 1.5rem;
      transition: background .3s;
    }
    .btn-adduser:hover {
      background: linear-gradient(120deg,#764ba2,#48d9ad 90%);
      color: #ffd700;
    }
    .modal-content {
      border-radius: 15px;
      background: #292a3c;
      color: #fff;
    }
    .modal label { color: #ffd700; }
    .modal .btn-action {width:100%;}
    .table th, .table td {vertical-align:middle;}
    @media (max-width:600px) {
      .manage-card {padding:1.3rem .4rem;}
      table td, table th {font-size:.97rem;}
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
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
          <li class="nav-item"><a class="nav-link active" href="manage_users.php">Manage Users</a></li>
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
    <h1>Manage Users</h1>
    <p>Add, edit, or remove users from your platform</p>
    <?php if (isset($_GET['msg'])): ?>
      <script>
        window.addEventListener('DOMContentLoaded', function(){
          showToast('<?= htmlspecialchars($_GET['msg']) ?>');
        });
      </script>
    <?php endif; ?>
  </section>

  <div class="manage-card shadow-lg">
    <!-- Add User Button -->
    <button class="btn btn-adduser mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="fas fa-user-plus me-2"></i>Add New User
    </button>

    <!-- Users Table -->
    <div class="table-responsive text-white">
      <table class="table align-middle text-center text-white table-hover">
        <thead class="text-white">
          <tr style="background:rgba(255,255,255,0.09);">
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created At</th>
            <th>Last Login</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="text-white">
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <span class="badge
                <?php if($user['role']=='admin'):?>badge-role-admin<?php endif; ?>
                <?php if($user['role']=='vendor'):?>badge-role-vendor<?php endif; ?>
                <?php if($user['role']=='client'):?>badge-role-client<?php endif; ?>">
                <?= ucfirst($user['role']) ?>
              </span>
            </td>
            <td>
              <span class="badge badge-status-<?= $user['status'] ?>">
                <?= ucfirst($user['status']) ?>
              </span>
            </td>
            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
            <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : '-' ?></td>
            <td>
              <button class="btn btn-sm btn-edit btn-action edit-user-btn"
                data-id="<?= $user['id'] ?>"
                data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>"
                data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>"
                data-role="<?= $user['role'] ?>"
                data-status="<?= $user['status'] ?>"
                data-bs-toggle="modal" data-bs-target="#editUserModal">
                <i class="fas fa-edit"></i>
              </button>
              <?php if($user['id'] != $_SESSION['user_id']): ?>
                <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-del btn-action" onclick="return confirm('Delete this user?')">
                  <i class="fas fa-trash"></i>
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="add_user" value="1">
          <div class="mb-3">
            <label>Username</label>
            <input type="text" class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label>Role</label>
            <select class="form-select" name="role" required>
              <?php foreach($roles as $val=>$roleName): ?>
                <option value="<?= $val ?>"><?= $roleName ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select class="form-select" name="status" required>
              <?php foreach($statuses as $val=>$statusName): ?>
                <option value="<?= $val ?>"><?= $statusName ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required autocomplete="new-password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-adduser btn-action w-100"><i class="fas fa-user-plus me-2"></i>Add User</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Single Edit User Modal -->
  <div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content" id="editUserForm">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" id="edit_user_id">
          <input type="hidden" name="edit_user" value="1">
          <div class="mb-3">
            <label>Username</label>
            <input type="text" class="form-control" name="username" id="edit_username" required>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" id="edit_email" required>
          </div>
          <div class="mb-3">
            <label>Role</label>
            <select class="form-select" name="role" id="edit_role" required>
              <?php foreach($roles as $val=>$roleName): ?>
                <option value="<?= $val ?>"><?= $roleName ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select class="form-select" name="status" id="edit_status" required>
              <?php foreach($statuses as $val=>$statusName): ?>
                <option value="<?= $val ?>"><?= $statusName ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Password (leave blank to keep unchanged)</label>
            <input type="password" class="form-control" name="password" id="edit_password" autocomplete="new-password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-edit btn-action w-100"><i class="fas fa-save me-2"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <div style="height:2rem;"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
    // Edit User Modal population
    const editUserModal = document.getElementById('editUserModal');
    document.querySelectorAll('.edit-user-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('edit_user_id').value = this.dataset.id;
        document.getElementById('edit_username').value = this.dataset.username;
        document.getElementById('edit_email').value = this.dataset.email;
        document.getElementById('edit_role').value = this.dataset.role;
        document.getElementById('edit_status').value = this.dataset.status;
        document.getElementById('edit_password').value = '';
      });
    });
    // Autofocus for modals
    editUserModal.addEventListener('shown.bs.modal',function(){
      let input = editUserModal.querySelector('input:not([type=hidden]),select,textarea');
      if(input) input.focus();
    });
  </script>
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
  <script>
  function showToast(msg) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.style.display = 'flex';
    setTimeout(()=>toast.style.display='none', 3000);
  }
  </script>
</body>
</html>
