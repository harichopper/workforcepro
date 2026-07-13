<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<meta name="description" content="WorkForce Pro – <?= e($pageTitle) ?>">
<title><?= e($pageTitle) ?> – WorkForce Pro</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<!-- ── Sidebar ── -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon"><i class="fa-solid fa-users"></i></div>
    <span class="brand-name">WorkForce Pro</span>
  </div>
  <nav class="sidebar-nav">
    <span class="nav-section-label">Main</span>
    <?php
    $nav = [
      ['page'=>'dashboard',   'icon'=>'fa-chart-line',      'label'=>'Dashboard'],
      ['page'=>'employees',   'icon'=>'fa-user-tie',         'label'=>'Employees'],
      ['page'=>'departments', 'icon'=>'fa-building',         'label'=>'Departments'],
      ['page'=>'designations','icon'=>'fa-sitemap',          'label'=>'Designations'],
    ];
    foreach ($nav as $n):
      $cls = $page === $n['page'] ? 'active' : '';
    ?>
    <a class="nav-link <?= $cls ?>" href="/index.php?page=<?= $n['page'] ?>">
      <span class="nav-icon"><i class="fa-solid <?= $n['icon'] ?>"></i></span><?= $n['label'] ?>
    </a>
    <?php endforeach; ?>
    <span class="nav-section-label" style="margin-top:12px">HR Operations</span>
    <?php
    $nav2 = [
      ['page'=>'attendance','icon'=>'fa-calendar-check','label'=>'Attendance'],
      ['page'=>'leaves',    'icon'=>'fa-umbrella-beach','label'=>'Leaves'],
      ['page'=>'salary',    'icon'=>'fa-wallet',         'label'=>'Salary & Payroll'],
    ];
    foreach ($nav2 as $n):
      $cls = $page === $n['page'] ? 'active' : '';
    ?>
    <a class="nav-link <?= $cls ?>" href="/index.php?page=<?= $n['page'] ?>">
      <span class="nav-icon"><i class="fa-solid <?= $n['icon'] ?>"></i></span><?= $n['label'] ?>
    </a>
    <?php endforeach; ?>
    <span class="nav-section-label" style="margin-top:12px">Admin</span>
    <?php
    $nav3 = [
      ['page'=>'audit-logs','icon'=>'fa-shield-halved','label'=>'Audit Logs'],
      ['page'=>'settings',  'icon'=>'fa-gear',          'label'=>'Settings'],
    ];
    foreach ($nav3 as $n):
      $cls = $page === $n['page'] ? 'active' : '';
    ?>
    <a class="nav-link <?= $cls ?>" href="/index.php?page=<?= $n['page'] ?>">
      <span class="nav-icon"><i class="fa-solid <?= $n['icon'] ?>"></i></span><?= $n['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    <a href="/index.php?page=profile" class="user-badge text-decoration-none">
      <div class="user-avatar">
        <?php if (!empty($user['avatar'])): ?>
          <img src="/uploads/<?= e($user['avatar']) ?>" alt="avatar">
        <?php else: ?>
          <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
        <?php endif; ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= e($user['name'] ?? '') ?></div>
        <div class="user-role"><?= e($user['role'] ?? '') ?></div>
      </div>
    </a>
    <a href="/logout.php" class="nav-link text-danger mt-2" style="color:var(--danger)">
      <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>Logout
    </a>
  </div>
</aside>

<!-- ── Top Bar ── -->
<header class="topbar">
  <button class="icon-btn mobile-toggle" style="display:none"><i class="fa-solid fa-bars"></i></button>
  <span class="page-title"><?= e($pageTitle) ?></span>
  <div class="topbar-actions">
    <button class="icon-btn" data-action="toggle-theme" title="Toggle theme">
      <i class="fa-solid fa-sun theme-icon"></i>
    </button>
    <!-- Notifications -->
    <div class="dropdown">
      <button class="icon-btn" data-bs-toggle="dropdown" aria-expanded="false" id="notifBtn">
        <i class="fa-solid fa-bell"></i>
        <?php if ($unread > 0): ?>
          <span class="badge-dot"></span>
        <?php endif; ?>
      </button>
      <div class="dropdown-menu dropdown-menu-end p-0 notif-dropdown border-0">
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);font-size:.85rem;font-weight:600;display:flex;justify-content:space-between">
          Notifications
          <span class="notif-badge" style="background:var(--danger);color:#fff;border-radius:10px;padding:0 6px;font-size:.72rem"><?= $unread > 0 ? $unread : '' ?></span>
        </div>
        <div class="notif-list"></div>
      </div>
    </div>
    <!-- Profile -->
    <div class="dropdown">
      <button class="icon-btn" data-bs-toggle="dropdown">
        <i class="fa-solid fa-user"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius)">
        <li><a class="dropdown-item" href="/index.php?page=profile" style="color:var(--text)"><i class="fa-solid fa-user me-2"></i>Profile</a></li>
        <li><a class="dropdown-item" href="/index.php?page=settings" style="color:var(--text)"><i class="fa-solid fa-gear me-2"></i>Settings</a></li>
        <li><hr class="dropdown-divider" style="border-color:var(--border)"></li>
        <li><a class="dropdown-item text-danger" href="/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</header>

<!-- ── Page Content ── -->
<main class="main-content">
  <div class="page-body fade-in">
    <?php
      $pageFile = __DIR__ . "/pages/{$page}.php";
      if (file_exists($pageFile)) require $pageFile;
      else echo '<h2>Page not found</h2>';
    ?>
  </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/resource.js"></script>
</body>
</html>
