
<?php require 'app/views/templates/headerPublic.php'; ?>
<div class="container mt-4" style="max-width: 560px;">
  <h3 class="mb-3">Sign in to your account</h3>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
  <?php endif; ?>

  <form method="post" action="/login/verify" autocomplete="off">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" maxlength="190" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <div class="d-grid gap-2">
      <button class="btn btn-primary" type="submit">Sign in</button>
      <a class="btn btn-outline-secondary" href="/register">Create new account</a>
    </div>
  </form>
</div>
<?php require 'app/views/templates/footer.php'; ?>
