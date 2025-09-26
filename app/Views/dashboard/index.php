<?php
// Espera desde el controlador:
// $titulo,$pageClass,$kpis,$dist,$ym,$currentSalaryAmount

$titulo    = $titulo    ?? 'Panel general';
$pageClass = $pageClass ?? 'page-dashboard';

// base_url defensivo
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');

require BASE_PATH . '/app/Views/layouts/header.php';

$ingreso_salario = (float)($kpis['ingresos'] ?? 0);
$gastos_mes      = (float)($kpis['egresos']  ?? 0);
$balance         = (float)($kpis['balance']  ?? ($ingreso_salario - $gastos_mes));
$bono_objetivos  = 400000;

// Distribución para barras (nombre => monto)
$cat = [];
if (!empty($dist)) foreach ($dist as $row) { $cat[$row['categoria_padre']] = (float)$row['total']; }
$total_gastos    = array_sum($cat) ?: 1;
$ahorro_planeado = $cat['Ahorros planeados'] ?? 0;
$tasa_ahorro     = max(0, min(100, round(($ahorro_planeado / max(1, $ingreso_salario)) * 100)));

$w = fn($m,$t)=> max(2,min(100,round(($m/max(1,$t))*100)));
?>
<style>
/* ====== ESTILO “NEÓN” (scopeado al dashboard) ====== */
.page-dashboard { opacity: 1 !important; filter: none !important; }
.page-dashboard body { background:#0b1220; color:#dbeafe; } /* no afecta al header */
.page-dashboard .dash-wrap{ max-width:1200px; margin:0 auto; }
.page-dashboard .dash-title{ font-size:30px; font-weight:800; margin:10px 0 18px; color:#e2e8f0; }

.page-dashboard .grid{ display:grid; gap:16px; }
.page-dashboard .grid-3{ grid-template-columns: repeat(3,1fr); }
.page-dashboard .grid-2-1{ grid-template-columns: 2fr 1fr; }

.page-dashboard .card,
.page-dashboard .kpi{
  background:#0e1425;
  border:1px solid #1e293b;
  box-shadow: 0 6px 14px rgba(0,0,0,.35);
  border-radius:14px;
  padding:18px;
  color:#dbeafe;
}
.page-dashboard .kpi .muted{ color:#93a5be; font-size:12px; }

.page-dashboard .money{ color:#22c55e; font-weight:800; font-size:24px; letter-spacing:.2px; }
.page-dashboard .negative{ color:#ef4444; font-weight:800; font-size:24px; letter-spacing:.2px; }
.page-dashboard .muted{ color:#93a5be; }

.page-dashboard .btn{
  display:inline-flex; align-items:center; gap:8px;
  padding:10px 14px; border-radius:10px; font-size:14px;
  border:1px solid #334155; color:#e2e8f0; background:#0b1220; text-decoration:none;
  box-shadow: 0 2px 8px rgba(0,0,0,.25);
  transition: transform .08s ease, background .15s ease;
}
.page-dashboard .btn:hover{ background:#121a2f; transform: translateY(-1px); }
.page-dashboard .btn-primary{
  background: linear-gradient(135deg,#6d28d9 0%, #4f46e5 50%, #2563eb 100%);
  border-color: transparent; color:#fff; font-weight:700;
}
.page-dashboard .btn-primary:hover{ filter: brightness(1.08); }

.page-dashboard .row{ display:flex; gap:10px; flex-wrap:wrap; }
.page-dashboard .chip{
  display:inline-flex; align-items:center; gap:8px;
  background:#0b1220; border:1px solid #334155; border-radius:9999px;
  padding:6px 10px; font-size:13px; color:#cbd5e1;
}

.page-dashboard .bar{ height:12px; width:100%; background:#12213a; border-radius:10px; overflow:hidden; }
.page-dashboard .fill-blue{ background:#60a5fa; height:100%; }
.page-dashboard .fill-violet{ background:#a78bfa; height:100%; }
.page-dashboard .fill-green{ background:#34d399; height:100%; }
.page-dashboard .fill-red{ background:#fb7185; height:100%; }

.page-dashboard .section-title{ font-weight:800; margin-bottom:8px; color:#e2e8f0; }

/* Dona */
.page-dashboard .donut{
  position:relative; width:120px; height:120px; border-radius:9999px;
  background:conic-gradient(#22c55e calc(var(--p,20)*1%), rgba(34,197,94,.12) 0);
  box-shadow: inset 0 0 0 1px #172036, 0 0 0 4px rgba(34,197,94,.05);
}
.page-dashboard .donut::before{
  content:""; position:absolute; inset:12px; background:#0e1425; border-radius:9999px;
  box-shadow: inset 0 0 0 1px #172036;
}
.page-dashboard .donut b{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#22c55e; font-size:20px; font-weight:800; }

/* Quick links */
.page-dashboard .quick-links{ display:grid; grid-template-columns: repeat(3,1fr); gap:10px; }

@media (max-width:980px){
  .page-dashboard .grid-3,
  .page-dashboard .grid-2-1{ grid-template-columns:1fr; }
  .page-dashboard .quick-links{ grid-template-columns:1fr; }
}

/* Flash */
.page-dashboard .flash{ padding:10px 12px; border-radius:10px; margin:8px 0; font-size:14px; }
.page-dashboard .flash.success{ background:rgba(16,185,129,.12); color:#a7f3d0; border:1px solid rgba(16,185,129,.35); }
.page-dashboard .flash.error{ background:rgba(239,68,68,.12); color:#fecaca; border:1px solid rgba(239,68,68,.35); }

/* ===== Modal básico ===== */
.page-dashboard .modal-mask{ position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,.5); z-index:3000; }
.page-dashboard .modal-card{ width:min(520px,92vw); background:#0e1425; color:#e5e7eb; border:1px solid #1f2937; border-radius:12px; padding:16px; }
.page-dashboard .modal-card h3{ margin:0; font-weight:700; }
.page-dashboard .modal-card .field{ display:block; margin-top:10px; }
.page-dashboard .modal-card input{
  width:100%; padding:10px; background:#0b1220; border:1px solid #334155; border-radius:8px; color:#e5e7eb;
}
</style>


<div class="dash-wrap <?= htmlspecialchars($pageClass) ?>">
  <h1 class="dash-title">Panel general</h1>
  <p class="muted" style="margin-top:-6px">
    Resumen del mes: balance, ingresos y gastos, distribución por categorías,
    ahorro planeado y accesos rápidos a reportes.
  </p>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="flash success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="flash error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <!-- ===== KPIs ===== -->
  <section class="grid grid-3">
    <div class="kpi">
      <div class="muted">Balance</div>
      <div class="money">COP <?= number_format($balance, 0, ',', '.') ?></div>
      <div class="muted">Ingresos - Gastos</div>
    </div>

    <div class="kpi">
      <div class="muted">Ingresos (mes)</div>
      <div class="money">COP <?= number_format($ingreso_salario, 0, ',', '.') ?> (Salario)</div>
      <div class="row" style="margin-top:10px;">
        <span class="chip">✅ Cumplimiento de objetivos:
          <span style="color:#c4b5fd">COP <?= number_format($bono_objetivos, 0, ',', '.') ?></span>
        </span>
      </div>

      <!-- Botón que abre el modal -->
      <div class="row" style="margin-top:12px;">
        <?php if ($currentSalaryAmount !== null): ?>
          <button class="btn btn-primary" id="btn-open-salary">Modificar salario</button>
        <?php else: ?>
          <button class="btn btn-primary" id="btn-open-salary">Agregar salario</button>
        <?php endif; ?>
        <a class="btn" href="<?= $baseUrl ?>/import/csv">Importar CSV</a>
      </div>

      <small class="muted" style="display:block;margin-top:6px;">
        El salario se aplicará automáticamente cada mes (crea/actualiza una transacción positiva “Salario”).
      </small>
    </div>

    <div class="kpi">
      <div class="muted">Gastos (mes)</div>
      <div class="negative">COP <?= number_format($gastos_mes, 0, ',', '.') ?></div>
      <div class="row" style="margin-top:12px;">
        <a class="btn btn-primary" href="<?= $baseUrl ?>/transactions">Agregar gasto</a>
        <form action="<?= $baseUrl ?>/rules/generate" method="POST">
          <button class="btn" type="submit">Generar desde reglas</button>
        </form>
      </div>
    </div>
  </section>

  <!-- ===== Distribución + Ahorros ===== -->
  <section class="grid grid-2-1" style="margin-top:16px;">
    <div class="card">
      <div class="row" style="justify-content:space-between;">
        <div class="section-title">Distribución de gastos (mes)</div>
        <div class="muted">Comparado con el mes anterior</div>
      </div>

      <div style="margin-top:12px; display:grid; gap:14px;">
        <?php $colors=['fill-blue','fill-violet','fill-green','fill-red']; $i=0;
        foreach(($cat?:[]) as $n=>$m):
          $width=$w($m,$total_gastos); $class=$colors[$i%count($colors)]; $i++; ?>
          <div>
            <div class="row" style="justify-content:space-between; font-size:13px;">
              <span><?= htmlspecialchars($n) ?></span><span class="muted">COP <?= number_format($m,0,',','.') ?></span>
            </div>
            <div class="bar"><div class="<?= $class ?>" style="width:<?= $width ?>%"></div></div>
          </div>
        <?php endforeach; if (empty($cat)): ?>
          <p class="muted">Aún no hay gastos este mes.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="section-title">Ahorros planeados</div>
      <div class="row" style="align-items:center; gap:16px;">
        <div class="donut" style="--p: <?= (int)$tasa_ahorro ?>;"><b><?= (int)$tasa_ahorro ?>%</b></div>
        <div>
          <div style="font-weight:700;">Regla: Ahorro 10% por quincena</div>
          <div class="muted">Meta: COP 8.000.000 • Estimada: 03/2025</div>
        </div>
      </div>

      <div class="row" style="margin-top:12px;">
        <span class="chip">📧 Factura Claro <span class="muted">• 20 de cada mes</span></span>
        <span class="chip">🌐 Internet <span class="muted">• 5 de cada mes</span></span>
      </div>

      <div style="margin-top:14px; border-top:1px solid #1e293b; padding-top:12px;">
        <div class="section-title">Pagos totales</div>
        <div style="display:grid; gap:6px; font-size:14px;">
          <div class="row" style="justify-content:space-between;"><span>1ª quincena</span><span>COP <?= number_format(round($gastos_mes*0.45),0,',','.') ?></span></div>
          <div class="row" style="justify-content:space-between;"><span>2ª quincena</span><span>COP <?= number_format(round($gastos_mes*0.55),0,',','.') ?></span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== Accesos rápidos ===== -->
  <section style="margin-top:16px;">
    <div class="quick-links">
      <a class="btn" href="<?= $baseUrl ?>/debts/compare">Comparador de deudas</a>
      <a class="btn" href="<?= $baseUrl ?>/reports/waterfall">Cascada del mes</a>
      <a class="btn" href="<?= $baseUrl ?>/reports/calendar">Calendario (mapa de calor)</a>
      <a class="btn" href="<?= $baseUrl ?>/planner">Planificador de quincena</a>
      <a class="btn" href="<?= $baseUrl ?>/savings">Proyección de ahorro</a>
      <a class="btn" href="<?= $baseUrl ?>/reports/monthly">Reporte del mes (narrado)</a>
    </div>
  </section>
</div>

<!-- ===== Modal de salario ===== -->
<div id="salary-modal" class="modal-mask" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="salary-title">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
      <h3 id="salary-title"><?= $currentSalaryAmount !== null ? 'Modificar salario' : 'Agregar salario' ?></h3>
      <button id="salary-close" class="btn" type="button">✕</button>
    </div>

    <form action="<?= $baseUrl ?>/settings/salary" method="POST" style="display:grid; gap:10px; margin-top:12px;">
      <label class="field">Monto de salario
        <input type="number" name="salary_amount" step="1" min="0"
               value="<?= $currentSalaryAmount !== null ? htmlspecialchars($currentSalaryAmount) : '' ?>"
               placeholder="Ej. 1500000" required>
      </label>

      <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:6px;">
        <button type="button" class="btn" id="salary-cancel">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </form>

    <p class="muted" style="margin:.5rem 0 0">
      Este valor se usará automáticamente cada mes para crear/actualizar tu ingreso “Salario”.
    </p>
  </div>
</div>

<script>
  // Modal salario
  (function(){
    const openBtn  = document.getElementById('btn-open-salary');
    const modal    = document.getElementById('salary-modal');
    const closeBtn = document.getElementById('salary-close');
    const cancelBtn= document.getElementById('salary-cancel');

    function show(){ modal.style.display='flex'; modal.setAttribute('aria-hidden','false'); }
    function hide(){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); }

    if (openBtn)  openBtn.addEventListener('click', e=>{ e.preventDefault(); show(); });
    if (closeBtn) closeBtn.addEventListener('click', hide);
    if (cancelBtn)cancelBtn.addEventListener('click', hide);
    modal?.addEventListener('click', e=>{ if (e.target===modal) hide(); });
    document.addEventListener('keydown', e=>{ if(e.key==='Escape') hide(); });
  })();
</script>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
