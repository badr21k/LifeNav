<?php require 'app/views/templates/header.php'; ?>
<div class="container mt-3">
  <h3>Edit Expense</h3>
  <form method="post" action="/essentials/update/<?= (int)$row['id'] ?>">
    <?= csrf_field() ?>
    <div class="row g-2">
      <div class="col-md-3"><label class="form-label">Date</label><input class="form-control" type="date" name="date" value="<?= htmlspecialchars($row['date']) ?>" required></div>
      <div class="col-md-3"><label class="form-label">Amount</label><input class="form-control" type="text" name="amount" value="<?= htmlspecialchars(number_format($row['amount_cents']/100,2)) ?>" required></div>
      <div class="col-md-2"><label class="form-label">Currency</label><input class="form-control" type="text" name="currency" value="<?= htmlspecialchars($row['currency']) ?>" maxlength="3"></div>
      <div class="col-md-4">
        <label class="form-label">Payment</label>
        <select class="form-select" name="payment_method_id">
          <option value="">—</option>
          <?php foreach ($pms as $p): $sel=((int)$p['id']===(int)($row['payment_method_id']??0))?'selected':''; ?>
            <option value="<?= (int)$p['id'] ?>" <?= $sel ?>><?= htmlspecialchars($p['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select class="form-select" id="cat" name="category_id" required>
          <?php foreach ($categories as $c): $sel=((int)$c['id']===(int)$row['category_id'])?'selected':''; ?>
            <option value="<?= (int)$c['id'] ?>" <?= $sel ?>><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Subcategory</label>
        <select class="form-select" id="sub" name="subcategory_id">
          <option value="">— None —</option>
          <?php foreach ($subsByCat as $cid=>$subs): foreach ($subs as $s): $sel=((int)$s['id'] === (int)($row['subcategory_id']??0))?'selected':''; ?>
            <option data-cat="<?= (int)$cid ?>" value="<?= (int)$s['id'] ?>" <?= $sel ?>><?= htmlspecialchars($s['name']) ?></option>
          <?php endforeach; endforeach; ?>
        </select>
      </div>
      <div class="col-md-4"><label class="form-label">Merchant</label><input class="form-control" type="text" name="merchant" value="<?= htmlspecialchars($row['merchant'] ?? '') ?>" maxlength="64"></div>
      <div class="col-md-8"><label class="form-label">Note</label><input class="form-control" type="text" name="note" value="<?= htmlspecialchars($row['note'] ?? '') ?>" maxlength="255"></div>
      <div class="col-12"><label class="form-label">Tags (comma-separated)</label>
        <?php $tags = array_map(fn($t)=>$t['name'],$rowTags); ?>
        <input class="form-control" type="text" name="tags" value="<?= htmlspecialchars(implode(', ',$tags)) ?>">
      </div>
    </div>
    <div class="mt-3">
      <button class="btn btn-primary">Update</button>
      <a class="btn btn-outline-secondary" href="/essentials">Cancel</a>
    </div>
  </form>
</div>
<script>
const cat=document.getElementById('cat'), sub=document.getElementById('sub');
function filterSubs(){ const c=cat.value; [...sub.options].forEach(o=>{const d=o.getAttribute('data-cat'); if(!d)return; o.hidden=(d!==c);});}
cat.addEventListener('change',filterSubs); filterSubs();
</script>
<?php require 'app/views/templates/footer.php'; ?>
