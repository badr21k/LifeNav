<?php require 'app/views/templates/headerPublic.php'; ?>
<div class="container mt-4" style="max-width: 560px;">
  <h3 class="mb-3">Create your account</h3>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <form method="post" action="/register/store" autocomplete="off">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="mb-3">
      <label class="form-label">Full name</label>
      <input type="text" name="name" class="form-control" maxlength="120" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" maxlength="190" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password <span class="text-muted">(min 8 chars)</span></label>
      <input type="password" name="password" class="form-control" minlength="8" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm password</label>
      <input type="password" name="password_confirm" class="form-control" minlength="8" required>
    </div>

    <div class="d-grid gap-2">
      <button class="btn btn-primary" type="submit">Create account</button>
      <a class="btn btn-outline-secondary" href="/login">Back to login</a>
    </div>
  </form>

  <p class="text-muted small mt-3 mb-0">
    By creating an account you agree to our terms of use.
  </p>
</div>
<?php require 'app/views/templates/footer.php'; ?>
