<?php
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');
?>
<style>
.wrap{max-width:900px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.row{display:flex;gap:12px;flex-wrap:wrap}
.field{flex:1 1 220px; display:flex; flex-direction:column; gap:6px; margin-bottom:12px}
.input, select{width:100%;background:#0b1220;border:1px solid #334155;color:#e5e7eb;border-radius:8px;padding:10px}
.btn{display:inline-flex;align-items:center;justify-content:center; padding:10px 14px;border-radius:8px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;text-decoration:none; min-width:110px}
.btn[disabled]{opacity:.6;cursor:not-allowed}
.btn:hover{background:#111827}
.btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
.form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px}
</style>

<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Editar transacción</h1>

  <form id="tx-edit-form" action="<?= $baseUrl ?>/transactions/update" method="post" class="card" autocomplete="off">
    <input type="hidden" name="id" value="<?= (int)$tx['id'] ?>">

    <div class="row">
      <div class="field" style="flex:1 1 220px">
        <label>Tipo</label>
        <select name="kind" id="kind" class="input" onchange="toggleCats()">
          <option value="expense" <?= ($type)==='expense'?'selected':'' ?>>Gasto</option>
          <option value="income"  <?= ($type)==='income'?'selected':''  ?>>Ingreso</option>
        </select>
      </div>

      <div class="field" style="flex:2 1 320px" id="catExpenseWrap">
        <label>Categoría (Egreso)</label>
        <select id="catExpense" name="category_id" class="input">
          <?php foreach ($catsExpense as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (int)$tx['category_id']===$c['id']?'selected':'' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field" style="flex:2 1 320px; display:none" id="catIncomeWrap">
        <label>Categoría (Ingreso)</label>
        <select id="catIncome" name="category_id" class="input" disabled>
          <?php foreach ($catsIncome as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (int)$tx['category_id']===$c['id']?'selected':'' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="field" style="flex:1 1 220px">
        <label>Fecha</label>
        <input class="input" type="date" name="date_at" value="<?= htmlspecialchars($tx['date_at']) ?>">
      </div>
      <div class="field" style="flex:1 1 220px">
        <label>Monto (COP)</label>
        <input
          class="input"
          id="amount"
          name="amount"
          type="text"
          inputmode="numeric"
          value="<?= htmlspecialchars(number_format(abs($tx['amount']),0,',','.')) ?>"
          placeholder="p.ej. 120.000">
      </div>
    </div>

    <div class="row">
      <div class="field" style="flex:1 1 100%">
        <label>Descripción</label>
        <input class="input" type="text" name="description" value="<?= htmlspecialchars($tx['description'] ?? '') ?>">
      </div>
    </div>

    <div class="form-actions">
      <a class="btn" href="<?= $baseUrl ?>/transactions">Cancelar</a>
      <button id="btnSave" class="btn btn-primary" type="submit">Guardar cambios</button>
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
    expWrap.style.display = 'block'; expSel.disabled=false;
    incWrap.style.display = 'none';  incSel.disabled=true;
  } else {
    expWrap.style.display = 'none';  expSel.disabled=true;
    incWrap.style.display = 'block'; incSel.disabled=false;
  }
}
toggleCats();

// Deshabilitar "Guardar" si no hay cambios
(function () {
  const form = document.getElementById('tx-edit-form');
  const save = document.getElementById('btnSave');
  if (!form || !save) return;

  const snap = {};
  new FormData(form).forEach((v,k)=> snap[k]=v);

  function changed() {
    const f = new FormData(form);
    for (const [k,v] of f.entries()) {
      if ((snap[k] ?? '') !== v) return true;
    }
    return false;
  }

  function toggle() { save.disabled = !changed(); }
  form.addEventListener('input',  toggle);
  form.addEventListener('change', toggle);
  toggle();
})();
</script>
