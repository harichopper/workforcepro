<?php
http_response_code(404);
require_once __DIR__ . '/includes/bootstrap.php';
?><!DOCTYPE html>
<html lang="en" data-theme="dark"><head><meta charset="UTF-8"><title>404 – WorkForce Pro</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/app.css">
</head><body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;text-align:center">
  <div style="font-size:6rem;font-weight:800;color:var(--primary)">404</div>
  <h2>Page Not Found</h2>
  <p class="text-muted">The page you're looking for doesn't exist.</p>
  <a href="/index.php" class="btn btn-primary"><i class="fa-solid fa-home"></i>Back to Dashboard</a>
</div>
</body></html>
