<?php
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');

$titulo    = $titulo    ?? 'Editar categoría';
$pageClass = $pageClass ?? 'page-categories-edit';

$kinds = [
  'expense' => 'Gasto',
  'income'  => 'Ingreso',
  'saving'  => 'Ahorro',
  'debt'    => 'Deuda',
];

$color = $category['color_hex'] ?? '#888888';
$presets = [
  ['#16a34a','Verde'],
  ['#1d4ed8','Azul'],
  ['#7c3aed','Violeta'],
  ['#f59e0b','Ámbar'],
  ['#dc2626','Rojo'],
];
$matchPreset = null;
foreach ($presets as $p) {
  if (strcasecmp($p[0], $color) === 0) { $matchPreset = $p[0]; break; }
}
$otherSelected = !$matchPreset;
$isActive = empty($category['is_archived']); // 1 si activa
?>
<style>
  .page-categories-edit .center-wrap{
    min-height:calc(100vh - 160px);
    display:flex;align-items:center;justify-content:center;
  }
  .page-categories-edit .form-card{
    width:min(900px, 92vw);
    background:#0f172a;border:1px solid #1f2937;border-radius:14px;
    padding:18px;color:#e5e7eb
  }
  .page-categories-edit h1{font-size:22px;margin:6px 0 12px}
  .page-categories-edit .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .page-categories-edit .field{display:flex;flex-direction:column;gap:6px}
  .page-categories-edit .field label{font-size:14px;color:#93a5be}
  .page-categories-edit .field input[type="text"],
  .page-categories-edit .field select{
    padding:10px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;
  }
  .page-categories-edit .colors-wrap{display:flex;flex-direction:column;gap:8px}
  .page-categories-edit .swatches{display:flex;flex-wrap:wrap;gap:10px}
  .page-categories-edit .swatch{
    position:relative;display:flex;align-items:center;gap:8px;padding:8px 10px;
    border:1px solid #334155;border-radius:10px;background:#0b1220;cursor:pointer
  }
  .page-categories-edit .swatch input{position:absolute;inset:0;opacity:0;cursor:pointer}
  .page-categories-edit .dot{
    width:18px;height:18px;border-radius:9999px;background:var(--c,#888);
    box-shadow:0 0 0 2px #0b1220, 0 0 0 3px #334155;
  }
  .page-categories-edit .swatch b{font-size:14px;color:#cbd5e1;font-weight:600}
  .page-categories-edit .swatch.selected{outline:2px solid #6366f1}
  .page-categories-edit .other-box{display:none;margin-top:6px;gap:8px;align-items:center}
  .page-categories-edit .other-box input[type="color"]{width:42px;height:36px;border:none;background:transparent}

  .page-categories-edit .checks{display:flex;gap:18px;align-items:center;margin-top:8px}
  .page-categories-edit .actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px}
  .page-categories-edit .btn{
    display:inline-flex;align-items:center;justify-content:center;min-width:120px;
    padding:10px 14px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;text-decoration:none
  }
  .page-categories-edit .btn:hover{background:#111827}
  .page-categories-edit .btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
  @media (max-width:900px){ .page-categories-edit .form-grid{grid-template-columns:1fr} }
</style>

<div class="center-wrap">
  <div class="form-card">
    <h1>Editar categoría</h1>

    <form action="<?= $baseUrl ?>/categories/update" method="POST" autocomplete="off">
      <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">

      <div class="form-grid">
        <div class="field">
          <label for="kind">Tipo</label>
          <select id="kind" name="kind" required>
            <?php foreach($kinds as $val=>$label): ?>
              <option value="<?= $val ?>" <?= $val===$category['kind']?'selected':'' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label for="name">Nombre</label>
          <input id="name" name="name" type="text" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>

        <div class="field colors-wrap" style="grid-column:1 / -1">
          <label>Color</label>
          <div class="swatches" id="swatches">
            <?php foreach($presets as [$hex,$label]): $isSel = ($matchPreset === $hex); ?>
              <label class="swatch <?= $isSel?'selected':'' ?>" data-value="<?= $hex ?>">
                <input type="radio" name="color_choice" value="<?= $hex ?>" <?= $isSel?'checked':'' ?>>
                <span class="dot" style="--c: <?= $hex ?>"></span>
                <b><?= $label ?></b>
              </label>
            <?php endforeach; ?>

            <label class="swatch <?= $otherSelected?'selected':'' ?>" data-value="__other" id="swatch-other">
              <input type="radio" name="color_choice" value="__other" <?= $otherSelected?'checked':'' ?>>
              <span class="dot" id="dot-other" style="--c:<?= htmlspecialchars($color) ?>"></span>
              <b>Otro…</b>
            </label>
          </div>

          <div class="other-box" id="other-box" style="<?= $otherSelected?'display:flex':'display:none' ?>">
            <input type="color" id="color-picker" value="<?= htmlspecialchars($color) ?>" aria-label="Elegir color">
            <input type="text" id="color-hex-visible" value="<?= htmlspecialchars($color) ?>"
                   style="width:120px;padding:10px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb">
          </div>

          <input type="hidden" name="color_hex" id="color_hex" value="<?= htmlspecialchars($color) ?>">
        </div>

        <div class="field" style="grid-column:1 / -1">
          <div class="checks">
            <label><input type="checkbox" name="is_active" value="1" <?= $isActive?'checked':'' ?>> Activa</label>
            <label><input type="checkbox" id="is_variable" name="is_variable" value="1" <?= !empty($category['is_variable'])?'checked':'' ?>> ¿Variable?</label>
          </div>
        </div>
      </div>

      <div class="actions">
        <a href="<?= $baseUrl ?>/categories" class="btn">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
  (function(){
    const swatches   = document.getElementById('swatches');
    const otherBox   = document.getElementById('other-box');
    const colorHex   = document.getElementById('color_hex');
    const picker     = document.getElementById('color-picker');
    const hexVisible = document.getElementById('color-hex-visible');
    const dotOther   = document.getElementById('dot-other');

    function selectSwatch(labelEl){
      document.querySelectorAll('.page-categories-edit .swatch').forEach(el => el.classList.remove('selected'));
      labelEl.classList.add('selected');

      const val = labelEl.getAttribute('data-value');
      if(val === '__other'){
        otherBox.style.display = 'flex';
        colorHex.value = (hexVisible.value.trim() || picker.value);
        dotOther.style.setProperty('--c', colorHex.value);
      }else{
        otherBox.style.display = 'none';
        colorHex.value = val;
      }
    }

    swatches.addEventListener('change', (e) => {
      const label = e.target.closest('.swatch');
      if(label) selectSwatch(label);
    });

    picker.addEventListener('input', () => {
      hexVisible.value = picker.value;
      colorHex.value   = picker.value;
      dotOther.style.setProperty('--c', picker.value);
    });
    hexVisible.addEventListener('input', () => {
      let val = hexVisible.value.trim();
      if (!val.startsWith('#')) val = '#' + val;
      colorHex.value = val;
      dotOther.style.setProperty('--c', val);
    });
  })();
</script>
