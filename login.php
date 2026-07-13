<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

// Handle auth
$auth = new AuthController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'login') {
    $auth->login();
}

if (isAuthenticated()) redirect('/index.php');

$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="WorkForce Pro – Enterprise HR Management System">
<title>Sign In – WorkForce Pro</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="login-page">
<div class="login-card">
  <div class="login-logo"><i class="fa-solid fa-users"></i></div>
  <h1>WorkForce Pro</h1>
  <p>Enterprise HR Management System</p>

  <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" style="border-radius:8px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#ef4444;font-size:.85rem">
      <i class="fa-solid fa-circle-exclamation"></i><?= e($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" id="loginForm">
    <input type="hidden" name="_action" value="login">
    <div class="mb-4">
      <label class="form-label">Email Address</label>
      <div style="position:relative">
        <i class="fa-solid fa-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted)"></i>
        <input type="email" name="email" class="form-control" style="padding-left:36px" placeholder="admin@workforce.test" value="admin@workforce.test" required>
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label">Password</label>
      <div style="position:relative">
        <i class="fa-solid fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted)"></i>
        <input type="password" name="password" id="pwdInput" class="form-control" style="padding-left:36px;padding-right:40px" placeholder="••••••••" value="password" required>
        <button type="button" onclick="togglePwd()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer"><i class="fa-solid fa-eye" id="pwdIcon"></i></button>
      </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 justify-content-center py-2 mb-3">
      <i class="fa-solid fa-sign-in-alt"></i> Sign In
    </button>
    <div style="font-size:.78rem;color:var(--muted);text-align:center">
      Demo: <code>admin@workforce.test</code> / <code>password</code>
    </div>
  </form>
</div>
<script>
function togglePwd() {
  const i = document.getElementById('pwdInput'), ic = document.getElementById('pwdIcon');
  i.type = i.type === 'password' ? 'text' : 'password';
  ic.className = i.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
}
</script>
<script src="/assets/js/app.js"></script>
</body>
</html>
