<?php
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');

$old    = $_SESSION['old'] ?? [];
$errors = $_SESSION['flash_errors'] ?? [];
unset($_SESSION['old'], $_SESSION['flash_errors']);

$currentUrl = $baseUrl . '/transactions/create?type=' . urlencode($type);
?>
<style>
.wrap{max-width:900px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.row{display:flex;gap:12px;flex-wrap:wrap}
.field{flex:1 1 220px; display:flex; flex-direction:column; gap:6px; margin-bottom:12px}
.input, select, textarea{width:100%;background:#0b1220;border:1px solid #334155;color:#e5e7eb;border-radius:8px;padding:10px}
.btn{display:inline-flex;align-items:center;justify-content:center; padding:10px 14px;border-radius:8px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;text-decoration:none; height:auto; min-width:110px}
.btn:hover{background:#111827}
.btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
.link{color:#93c5fd;text-decoration:none}
.link:hover{text-decoration:underline}
.alert{border-radius:8px;padding:10px 12px;margin-bottom:12px}
.alert-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.35);color:#fecaca}
.help{font-size:.9rem;color:#93a5be}
.form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px}
</style>

<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Agregar transacción</h1>

  <?php if ($errors): ?>
    <div class="alert alert-error">
      <ul style="margin:0;padding-left:18px"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form action="<?= $baseUrl ?>/transactions" method="post" class="card">
    <div class="row">
      <div class="field" style="flex:1 1 220px">
        <label>Tipo</label>
        <select name="kind" id="kind" class="input" onchange="toggleCats()">
          <option value="expense" <?= ($old['kind'] ?? $type)==='expense'?'selected':'' ?>>Gasto</option>
          <option value="income"  <?= ($old['kind'] ?? $type)==='income'?'selected':''  ?>>Ingreso</option>
        </select>
      </div>

      <div class="field" style="flex:2 1 320px" id="catExpenseWrap">
        <label>Categoría (Egreso)</label>
        <select id="catExpense" name="category_id" class="input">
          <option value="0">Seleccione…</option>
          <?php foreach ($catsExpense as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (int)($old['category_id'] ?? 0)===$c['id']?'selected':'' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="help">
          ¿No está la categoría?
          <a class="link" href="<?= $baseUrl ?>/categories/create?redirect=<?= urlencode($currentUrl) ?>">Crear nueva categoría</a>
          • <a class="link" href="<?= $baseUrl ?>/categories">Ver todas las categorías</a>
        </div>
      </div>

      <div class="field" style="flex:2 1 320px; display:none" id="catIncomeWrap">
        <label>Categoría (Ingreso)</label>
        <select id="catIncome" name="category_id" class="input" disabled>
          <option value="0">Seleccione…</option>
          <?php foreach ($catsIncome as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="help">
          ¿No está la categoría?
          <a class="link" href="<?= $baseUrl ?>/categories/create?redirect=<?= urlencode($currentUrl) ?>">Crear nueva categoría</a>
          • <a class="link" href="<?= $baseUrl ?>/categories">Ver todas las categorías</a>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="field" style="flex:1 1 220px">
        <label>Fecha</label>
        <input class="input" type="date" name="date_at" value="<?= htmlspecialchars($old['date_at'] ?? $today) ?>">
      </div>
      <div class="field" style="flex:1 1 220px">
        <label>Monto (COP)</label>
        <input class="input" type="text" name="amount" placeholder="p.ej. 120.000"
               value="<?= htmlspecialchars($old['amount'] ?? '') ?>">
      </div>
    </div>

    <div class="row">
      <div class="field" style="flex:1 1 100%">
        <label>Descripción</label>
        <input class="input" type="text" name="description" placeholder="Opcional"
               value="<?= htmlspecialchars($old['description'] ?? '') ?>">
      </div>
    </div>

    <div class="form-actions">
      <a class="btn" href="<?= $baseUrl ?>/transactions">Cancelar</a>
      <button class="btn btn-primary" type="submit">Guardar</button>
    </div>
  </form>
</div>

<script>
function toggleCats() {
  const kind = document.getElementById('kind').value;
  const expWrap = document.getElementById('catExpenseWrap');
  const incWrap = document.getElementById('catIncomeWrap');
  const expSel  = document.getElementById('catExpense');
  const incSel  = document.getElementById('catIncome');

  if (kind === 'expense') {
    expWrap.style.display = 'block';
    incWrap.style.display = 'none';
    expSel.disabled = false;
    incSel.disabled = true;
  } else {
    expWrap.style.display = 'none';
    incWrap.style.display = 'block';
    expSel.disabled = true;
    incSel.disabled = false;
  }
}
toggleCats();
</script>

