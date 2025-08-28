<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3">
  <h3><?= htmlspecialchars($title) ?></h3>
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2 mt-2">
    <?php foreach ($tabs as $t): ?>
      <div class="col">
        <a class="btn btn-outline-primary w-100 py-3" href="/mode/tab/<?= htmlspecialchars($t['mode']) ?>/<?= (int)$t['id'] ?>">
          <?= htmlspecialchars($t['name']) ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require 'app/views/templates/footer.php'; ?>
