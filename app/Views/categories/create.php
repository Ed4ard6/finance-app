<?php
// app/Views/categories/create.php
// Variables que puedes setear desde el controlador si quieres
$titulo    = $titulo    ?? 'Nueva categoría';
$pageClass = $pageClass ?? 'page-categories-create';

$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');

// Valores por defecto del formulario
$defaultKind = $_GET['kind'] ?? 'expense'; // expense|income|saving|debt
$kinds = [
  'expense' => 'Gasto',
  'income'  => 'Ingreso',
  'saving'  => 'Ahorro',
  'debt'    => 'Deuda',
];
?>
<style>
  .form-card{max-width:900px;margin:0 auto;background:#0f172a;border:1px solid #1f2937;border-radius:14px;padding:18px;color:#e5e7eb}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .field{display:flex;flex-direction:column;gap:6px}
  .field label{font-size:14px;color:#93a5be}
  .field input[type="text"],
  .field input[type="number"],
  .field input[type="date"],
  .field select{
    padding:10px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;
  }
  .actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px}
  .btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;text-decoration:none;min-width:120px}
  .btn:hover{background:#111827}
  .btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
  .colors-wrap{display:flex;flex-direction:column;gap:8px}
  .swatches{display:flex;flex-wrap:wrap;gap:10px}
  .swatch{
    position:relative;display:flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid #334155;border-radius:10px;background:#0b1220;cursor:pointer;
  }
  .swatch input{position:absolute;inset:0;opacity:0;cursor:pointer}
  .dot{
    width:18px;height:18px;border-radius:9999px;background:var(--c,#888);box-shadow:0 0 0 2px #0b1220, 0 0 0 3px #334155;
  }
  .swatch b{font-size:14px;color:#cbd5e1;font-weight:600}
  .swatch.selected{outline:2px solid #6366f1}
  .other-box{display:none;margin-top:6px;gap:8px;align-items:center}
  .other-box input[type="color"]{width:42px;height:36px;border:none;background:transparent}
  .check-var{display:flex;align-items:center;gap:10px;margin-top:8px}
  @media (max-width:900px){ .form-grid{grid-template-columns:1fr} }
</style>

<div class="form-card">
  <h1 style="font-size:22px;margin:6px 0 12px">Nueva categoría</h1>

  <form action="<?= $baseUrl ?>/categories" method="POST" autocomplete="off">
    <div class="form-grid">
      <div class="field">
        <label for="kind">Tipo</label>
        <select id="kind" name="kind" required>
          <?php foreach($kinds as $val=>$label): ?>
            <option value="<?= $val ?>" <?= $val===$defaultKind?'selected':'' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="name">Nombre</label>
        <input id="name" name="name" type="text" placeholder="Ej. Comida / Internet / Salario" required>
      </div>

      <div class="field colors-wrap" style="grid-column:1 / -1">
        <label>Color</label>

        <!-- swatches visuales -->
        <div class="swatches" id="swatches">
          <?php
            $presets = [
              ['#16a34a','Verde'],
              ['#1d4ed8','Azul'],
              ['#7c3aed','Violeta'],
              ['#f59e0b','Ámbar'],
              ['#dc2626','Rojo'],
            ];
            $first = true;
            foreach($presets as [$hex,$label]): ?>
              <label class="swatch <?= $first?'selected':'' ?>" data-value="<?= $hex ?>">
                <input type="radio" name="color_choice" value="<?= $hex ?>" <?= $first?'checked':'' ?>>
                <span class="dot" style="--c: <?= $hex ?>"></span>
                <b><?= $label ?></b>
              </label>
          <?php $first=false; endforeach; ?>

          <!-- Opción Otro -->
          <label class="swatch" data-value="__other" id="swatch-other">
            <input type="radio" name="color_choice" value="__other">
            <span class="dot" id="dot-other" style="--c:#888888"></span>
            <b>Otro…</b>
          </label>
        </div>

        <!-- Picker para "Otro" -->
        <div class="other-box" id="other-box">
          <input type="color" id="color-picker" value="#888888" aria-label="Elegir color">
          <input type="text" id="color-hex-visible" value="#888888" style="width:120px;padding:10px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e5e7eb">
        </div>

        <!-- valor final que se enviará -->
        <input type="hidden" name="color_hex" id="color_hex" value="#16a34a">
      </div>

      <div class="field" style="grid-column:1 / -1">
        <div class="check-var">
          <input type="checkbox" id="is_variable" name="is_variable" value="1">
          <label for="is_variable" style="color:#cbd5e1">¿Variable? <span style="color:#93a5be">(opcional)</span></label>
        </div>
      </div>
    </div>

    <div class="actions">
      <a href="<?= $baseUrl ?>/categories" class="btn">Cancelar</a>
      <button type="submit" class="btn btn-primary">Guardar</button>
    </div>
  </form>
</div>

<script>
  (function(){
    const swatches   = document.getElementById('swatches');
    const otherBox   = document.getElementById('other-box');
    const colorHex   = document.getElementById('color_hex'); // hidden final
    const picker     = document.getElementById('color-picker');
    const hexVisible = document.getElementById('color-hex-visible');
    const dotOther   = document.getElementById('dot-other');

    // estado inicial
    otherBox.style.display = 'none';

    function selectSwatch(labelEl){
      // marcar visualmente
      document.querySelectorAll('.swatch').forEach(el => el.classList.remove('selected'));
      labelEl.classList.add('selected');

      const val = labelEl.getAttribute('data-value');
      if(val === '__other'){
        otherBox.style.display = 'flex';
        colorHex.value = hexVisible.value.trim() || picker.value;
        dotOther.style.setProperty('--c', colorHex.value);
      }else{
        otherBox.style.display = 'none';
        colorHex.value = val;
      }
    }

    // click en swatches
    swatches.addEventListener('change', (e) => {
      const label = e.target.closest('.swatch');
      if(label) selectSwatch(label);
    });

    // sincronizar picker/hex visible
    picker.addEventListener('input', () => {
      hexVisible.value = picker.value;
      colorHex.value = picker.value;
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
