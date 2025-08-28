<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3">
  <div class="d-flex align-items-center justify-content-between">
    <h3><?= htmlspecialchars($title) ?></h3>
    <a class="btn btn-outline-secondary" href="/mode/<?= htmlspecialchars($tab['mode']) ?>">Back</a>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger mt-2"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <div class="mt-3 d-flex flex-wrap gap-2">
    <?php foreach ($categories as $c): ?>
      <form method="post" action="/mode/select/<?= htmlspecialchars($tab['mode']) ?>/<?= (int)$tab['id'] ?>/<?= (int)$c['id'] ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline-primary">
          <?= $c['is_custom'] ? '⭐ ' : '' ?><?= htmlspecialchars($c['name']) ?>
        </button>
      </form>
    <?php endforeach; ?>
  </div>

  <hr class="my-4">
  <h5>Add custom category</h5>
  <form class="row g-2" method="post" action="/mode/select/<?= htmlspecialchars($tab['mode']) ?>/<?= (int)$tab['id'] ?>/0"
        onsubmit="return false;">
    <div class="col-md-6">
      <input class="form-control" id="customName" placeholder="Custom category name">
    </div>
    <div class="col-md-6">
      <button type="button" class="btn btn-secondary" onclick="createCustom()">Create & Select</button>
    </div>
  </form>
</div>

<script>
async function createCustom() {
  const name = document.getElementById('customName').value.trim();
  if (!name) return alert('Enter a name');
  try {
    const res = await fetch('/tools/custom-category', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({tab_id: <?= (int)$tab['id'] ?>, name})
    });
    const j = await res.json();
    if (j.ok) { window.location.href = '/mode/select/<?= htmlspecialchars($tab['mode']) ?>/<?= (int)$tab['id'] ?>/'+j.category_id; }
    else alert(j.error || 'Failed');
  } catch(e){ alert('Network error'); }
}
</script>
<?php require 'app/views/templates/footer.php'; ?>
