<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
// Demo users and awards (static for frontend only)
$users = [
  ['id'=>1,'username'=>'sarah_designs','email'=>'sarah@designhub.com','role'=>'vendor','status'=>'active'],
  ['id'=>2,'username'=>'ahmed_creative','email'=>'ahmed@designhub.com','role'=>'vendor','status'=>'active'],
  ['id'=>3,'username'=>'client1','email'=>'client1@example.com','role'=>'client','status'=>'active'],
  ['id'=>4,'username'=>'client2','email'=>'client2@example.com','role'=>'client','status'=>'active'],
  ['id'=>5,'username'=>'nour_graphics','email'=>'nour@designhub.com','role'=>'vendor','status'=>'suspended'],
];
$recent_awards = [
  ['user'=>'sarah_designs','role'=>'vendor','award'=>'Top Designer','note'=>'Outstanding logo work','date'=>'2024-06-01'],
  ['user'=>'client1','role'=>'client','award'=>'Super Client','note'=>'Great communication','date'=>'2024-05-28'],
  ['user'=>'ahmed_creative','role'=>'vendor','award'=>'Fast Delivery','note'=>'Delivered project ahead of schedule','date'=>'2024-05-25'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Awarding – DesignHub Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
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
    .awards-hero {
      padding:4.5rem 1rem 2.2rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .awards-hero h1 {
      font-size:2.2rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .awards-hero p {font-size:1.08rem;opacity:.85;}
    .awards-card {
      background:rgba(255,255,255,0.13);
      backdrop-filter:blur(12px);
      border-radius:20px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      padding:2.5rem 2rem 2rem 2rem;
      max-width:1100px;
      margin:2rem auto 2.5rem auto;
      animation:float 7s ease-in-out infinite;
    }
    .awards-card h3 {font-size:1.5rem;font-weight:700;margin-bottom:1.2rem;color:#ffd700;}
    .table-awards th, .table-awards td {
      border-color:rgba(255,255,255,0.08);
      vertical-align:middle;
      font-size:1.02rem;
      background:rgba(255,255,255,0.05);
      color:#fff;
    }
    .table-awards tr:hover td {background:rgba(255,255,255,0.13);transition:background .2s;}
    .btn-award {
      background:linear-gradient(90deg,#ffd700,#ff537e 99%);
      color:#253053;border:none;
      border-radius:22px;font-size:.97rem;font-weight:600;box-shadow:0 1px 10px #0001;
      padding:6px 18px;
      transition:background .3s;
    }
    .btn-award:hover {background:linear-gradient(120deg,#ff537e,#ffd700 90%);color:#fff;}
    .badge-role-admin    {background:#ff537e;color:#fff;}
    .badge-role-vendor   {background:#ffd700;color:#253053;}
    .badge-role-client   {background:#48d9ad;color:#fff;}
    .badge-status-active {background:#48d9ad;color:#fff;}
    .badge-status-inactive {background:#ffc107;color:#253053;}
    .badge-status-suspended {background:#dc3545;color:#fff;}
    .recent-awards-list {
      margin-top:2.5rem;
      background:rgba(255,255,255,0.10);
      border-radius:18px;
      padding:2rem 1.5rem;
      box-shadow:0 8px 32px rgba(0,0,0,0.13);
    }
    .recent-award-item {
      display:flex;align-items:center;gap:1.2rem;
      padding:.9rem 0;
      border-bottom:1px solid rgba(255,255,255,0.08);
    }
    .recent-award-item:last-child {border-bottom:none;}
    .recent-award-icon {
      font-size:2.1rem;
      color:#ffd700;
      filter:drop-shadow(0 2px 8px #ffd70088);
    }
    .recent-award-user {font-weight:700;color:#fff;}
    .recent-award-role {font-size:.97rem;color:#ffd700;}
    .recent-award-note {font-size:.98rem;color:#fff;opacity:.85;}
    .recent-award-date {font-size:.93rem;color:#ccc;margin-left:auto;}
    @media (max-width:900px) {
      .awards-card {padding:1.2rem .5rem;}
      .recent-awards-list {padding:1.2rem .5rem;}
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
          <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_services.php">Manage Services</a></li>
          <li class="nav-item"><a class="nav-link" href="site_settings.php">Site Settings</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link active" href="awards.php">Awarding</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <section class="awards-hero">
    <div class="alert alert-warning text-center fw-bold" style="font-size:1.2rem;background:rgba(255,215,0,0.95);color:#764ba2;border:none;letter-spacing:1px;box-shadow:0 2px 12px #ffd70033;">Coming Soon</div>
    <h1>Awarding</h1>
    <p>Recognize and celebrate outstanding users and vendors. Assign awards for excellence, communication, and more.</p>
  </section>
  <div class="awards-card shadow-lg">
    <h3><i class="fas fa-trophy me-2"></i>Users</h3>
    <div class="mb-3">
      <input type="text" class="form-control" id="userSearch" placeholder="Search users by name, email, or role..." oninput="filterUsers()">
    </div>
    <div class="table-responsive">
      <table class="table table-awards align-middle text-center">
        <thead>
          <tr style="background:rgba(255,255,255,0.09);">
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Award</th>
          </tr>
        </thead>
        <tbody id="usersTable">
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge badge-role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
            <td><span class="badge badge-status-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
            <td>
              <button class="btn btn-award" onclick="openAwardModal('<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')"><i class="fas fa-trophy me-1"></i>Award</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="awards-card shadow-lg recent-awards-list">
    <h3><i class="fas fa-star me-2"></i>Recent Awards</h3>
    <?php foreach ($recent_awards as $a): ?>
      <div class="recent-award-item">
        <span class="recent-award-icon"><i class="fas fa-trophy"></i></span>
        <span class="recent-award-user"><?= htmlspecialchars($a['user']) ?></span>
        <span class="recent-award-role">(<?= ucfirst($a['role']) ?>)</span>
        <span class="recent-award-note">&ldquo;<?= htmlspecialchars($a['award']) ?><?php if ($a['note']): ?>: <?= htmlspecialchars($a['note']) ?><?php endif; ?>&rdquo;</span>
        <span class="recent-award-date"><i class="fas fa-calendar-alt me-1"></i><?= $a['date'] ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <!-- Award Modal -->
  <div class="modal fade" id="awardModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" onsubmit="return submitAward();">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-trophy me-2"></i>Award User</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="awardUsername">
          <div class="mb-3">
            <label class="form-label">User</label>
            <input type="text" class="form-control" id="awardUserDisplay" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Award Type</label>
            <select class="form-select" id="awardType" required>
              <option value="">Select Award</option>
              <option>Top Designer</option>
              <option>Super Client</option>
              <option>Fast Delivery</option>
              <option>Excellent Communication</option>
              <option>Project of the Month</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Note (optional)</label>
            <textarea class="form-control" id="awardNote" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-award w-100"><i class="fas fa-trophy me-2"></i>Submit Award</button>
        </div>
      </form>
    </div>
  </div>
  <div id="toast" class="toast-notify"></div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
    // User search
    function filterUsers() {
      const q = document.getElementById('userSearch').value.trim().toLowerCase();
      document.querySelectorAll('#usersTable tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (!q || text.includes(q)) ? '' : 'none';
      });
    }
    // Award modal logic
    function openAwardModal(username) {
      document.getElementById('awardUsername').value = username;
      document.getElementById('awardUserDisplay').value = username;
      document.getElementById('awardType').value = '';
      document.getElementById('awardNote').value = '';
      var modal = new bootstrap.Modal(document.getElementById('awardModal'));
      modal.show();
    }
    function submitAward() {
      const user = document.getElementById('awardUsername').value;
      const type = document.getElementById('awardType').value;
      if (!type) {
        showToast('Please select an award type.');
        return false;
      }
      showToast('Awarded "' + type + '" to ' + user + ' (demo only)');
      bootstrap.Modal.getInstance(document.getElementById('awardModal')).hide();
      return false;
    }
    function showToast(msg) {
      const toast = document.getElementById('toast');
      toast.textContent = msg;
      toast.style.display = 'flex';
      setTimeout(()=>toast.style.display='none', 3000);
    }
  </script>
</body>
</html> 