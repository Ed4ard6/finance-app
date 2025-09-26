<?php
// Variables del controlador:
// $titulo, $pageClass, $kpis, $dist, $ym, $salaryCategoryId, $hasSalary

$titulo    = $titulo    ?? 'Panel general';
$pageClass = $pageClass ?? 'page-dashboard';
require BASE_PATH . '/app/Views/layouts/header.php';

// Fallbacks (por si algo viniera vacío)
$ingreso_salario = isset($kpis['ingresos']) ? (float)$kpis['ingresos'] : 0;
$gastos_mes      = isset($kpis['egresos'])  ? (float)$kpis['egresos']  : 0;
$balance         = isset($kpis['balance'])  ? (float)$kpis['balance']  : ($ingreso_salario - $gastos_mes);

// “Bono de objetivos” es demostrativo hasta integrar reglas/bonos
$bono_objetivos  = 400000;

// Distribución: arma array nombre=>monto a partir de $dist
$cat = [];
if (!empty($dist)) {
  foreach ($dist as $row) {
    $cat[$row['categoria_padre']] = (float)$row['total'];
  }
}
$total_gastos    = array_sum($cat) ?: 1;
$ahorro_planeado = $cat['Ahorros planeados'] ?? 0;
$tasa_ahorro     = max(0, min(100, round(($ahorro_planeado / max(1, $ingreso_salario)) * 100)));

$w = function ($monto, $total) { return max(2, min(100, round(($monto / max(1,$total)) * 100))); };
?>
<style>
/* ====== RESET/REVERT DE ESTÉTICA ====== */
/* Fuerza a que esta página NO tenga opacidades heredadas de modales/overlays */
.page-dashboard { opacity: 1 !important; filter: none !important; }

/* Fondo general y tipografía más nítida */
body { background:#0b1220; color:#dbeafe; }
.dash-wrap{ max-width:1200px; margin:0 auto; }

/* Título principal como en el mock original */
.dash-title{ font-size:30px; font-weight:800; margin:10px 0 18px; color:#e2e8f0; }

/* Grids */
.grid{ display:grid; gap:16px; }
.grid-3{ grid-template-columns: repeat(3,1fr); }
.grid-2-1{ grid-template-columns: 2fr 1fr; }

/* Cards/KPIs con contraste y bordes sutiles */
.card,.kpi{
  background:#0e1425;               /* más oscuro que antes */
  border:1px solid #1e293b;          /* borde nítido */
  box-shadow: 0 6px 14px rgba(0,0,0,.35);
  border-radius:14px;
  padding:18px;
  color:#dbeafe;
}
.kpi .muted{ color:#93a5be; font-size:12px; }

/* Paleta “neón” estilo dashboard: */
.money{ color:#22c55e; font-weight:800; font-size:24px; letter-spacing:.2px; }
.negative{ color:#ef4444; font-weight:800; font-size:24px; letter-spacing:.2px; }
.muted{ color:#93a5be; }

/* Botones vibrantes */
.btn{
  display:inline-flex; align-items:center; gap:8px;
  padding:10px 14px; border-radius:10px; font-size:14px;
  border:1px solid #334155; color:#e2e8f0; background:#0b1220; text-decoration:none;
  box-shadow: 0 2px 8px rgba(0,0,0,.25);
  transition: transform .08s ease, background .15s ease;
}
.btn:hover{ background:#121a2f; transform: translateY(-1px); }
.btn-primary{
  background: linear-gradient(135deg,#6d28d9 0%, #4f46e5 50%, #2563eb 100%);
  border-color: transparent; color:#fff; font-weight:700;
}
.btn-primary:hover{ filter: brightness(1.08); }

/* Chips */
.row{ display:flex; gap:10px; flex-wrap:wrap; }
.chip{
  display:inline-flex; align-items:center; gap:8px;
  background:#0b1220; border:1px solid #334155; border-radius:9999px;
  padding:6px 10px; font-size:13px; color:#cbd5e1;
}

/* Barras de distribución con colores llamativos */
.bar{ height:12px; width:100%; background:#12213a; border-radius:10px; overflow:hidden; }
.fill-blue{ background:#60a5fa; height:100%; }
.fill-violet{ background:#a78bfa; height:100%; }
.fill-green{ background:#34d399; height:100%; }
.fill-red{ background:#fb7185; height:100%; }

/* Secciones */
.section-title{ font-weight:800; margin-bottom:8px; color:#e2e8f0; }

/* Dona “neón” sin JS */
.donut{
  position:relative; width:120px; height:120px; border-radius:9999px;
  background:conic-gradient(#22c55e calc(var(--p,20)*1%), rgba(34,197,94,.12) 0);
  box-shadow: inset 0 0 0 1px #172036, 0 0 0 4px rgba(34,197,94,.05);
}
.donut::before{
  content:""; position:absolute; inset:12px; background:#0e1425; border-radius:9999px;
  box-shadow: inset 0 0 0 1px #172036;
}
.donut b{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#22c55e; font-size:20px; font-weight:800; }

/* Inputs inline para salario */
.kpi form input[type="number"]{
  appearance:textfield;
  padding:9px 10px; background:#0b1220; border:1px solid #334155; border-radius:8px;
  color:#e2e8f0; min-width:150px;
}
.kpi form input[type="number"]::placeholder{ color:#64748b; }

/* Quick links */
.quick-links{ display:grid; grid-template-columns: repeat(3,1fr); gap:10px; }

@media (max-width:980px){
  .grid-3,.grid-2-1{ grid-template-columns:1fr; }
  .quick-links{ grid-template-columns:1fr; }
}

/* Flash messages */
.flash{ padding:10px 12px; border-radius:10px; margin:8px 0; font-size:14px; }
.flash.success{ background:rgba(16,185,129,.12); color:#a7f3d0; border:1px solid rgba(16,185,129,.35); }
.flash.error{ background:rgba(239,68,68,.12); color:#fecaca; border:1px solid rgba(239,68,68,.35); }
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

  <!-- KPIs -->
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

      <!-- BOTÓN inteligente: Agregar vs Modificar -->
      <form action="/dashboard/salary" method="POST" class="row" style="margin-top:12px;">
        <input type="hidden" name="ym" value="<?= htmlspecialchars($ym) ?>">
        <input type="number" name="amount" step="1" min="0" placeholder="Monto salario" required>
        <input type="number" name="category_id" min="1" placeholder="ID categoría salario"
               value="<?= htmlspecialchars($salaryCategoryId ?? '') ?>"
               title="Si se deja vacío intento detectar 'Salario' automáticamente.">
        <button type="submit" class="btn btn-primary">
          <?= !empty($hasSalary) ? 'Modificar ingreso' : 'Agregar ingreso' ?>
        </button>
        <a class="btn" href="#">Importar CSV</a>
      </form>
    </div>

    <div class="kpi">
      <div class="muted">Gastos (mes)</div>
      <div class="negative">COP <?= number_format($gastos_mes, 0, ',', '.') ?></div>
      <div class="row" style="margin-top:12px;">
        <a class="btn btn-primary" href="/transactions">Agregar gasto</a>
        <a class="btn" href="/rules">Generar desde reglas</a>
      </div>
    </div>
  </section>

  <!-- Distribución + Ahorros -->
  <section class="grid grid-2-1" style="margin-top:16px;">
    <div class="card">
      <div class="row" style="justify-content:space-between;">
        <div class="section-title">Distribución de gastos (mes)</div>
        <div class="muted">Comparado con el mes anterior</div>
      </div>

      <div style="margin-top:12px; display:grid; gap:14px;">
        <?php
        $colors = ['fill-blue','fill-violet','fill-green','fill-red'];
        $i=0; foreach(($cat ?: []) as $nombre=>$monto):
          $width = $w($monto, $total_gastos);
          $class = $colors[$i % count($colors)]; $i++;
        ?>
          <div>
            <div class="row" style="justify-content:space-between; font-size:13px;">
              <span><?= htmlspecialchars($nombre) ?></span>
              <span class="muted">COP <?= number_format($monto,0,',','.') ?></span>
            </div>
            <div class="bar"><div class="<?= $class ?>" style="width:<?= $width ?>%"></div></div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($cat)): ?>
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

  <!-- Accesos rápidos -->
  <section style="margin-top:16px;">
    <div class="quick-links">
      <a class="btn" href="/debts/compare">Comparador de deudas</a>
      <a class="btn" href="/reports/waterfall">Cascada del mes</a>
      <a class="btn" href="/reports/calendar">Calendario (mapa de calor)</a>
      <a class="btn" href="/planner">Planificador de quincena</a>
      <a class="btn" href="/savings">Proyección de ahorro</a>
      <a class="btn" href="/reports/monthly">Reporte del mes (narrado)</a>
    </div>
  </section>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
