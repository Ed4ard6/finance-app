<?php
$titulo    = $titulo    ?? 'Panel general';
$pageClass = $pageClass ?? 'page-dashboard-mock';
require BASE_PATH . '/app/Views/layouts/header.php';

/* ===== DATOS MOCK (ajusta libremente hasta conectar BD) ===== */
$ingreso_salario   = 1500000;
$bono_objetivos    = 400000;
$gastos_mes        = 1100000;

$cat = [
  'Comida'            => 450000,
  'Servicios'         => 180000,
  'Ahorros planeados' => 150000,
  'Otros'             => 40000,
];

$total_gastos = array_sum($cat) ?: 1;
$ahorro_planeado = $cat['Ahorros planeados'] ?? 0;
$tasa_ahorro = max(0, min(100, round(($ahorro_planeado / max(1, $ingreso_salario)) * 100)));
$balance = $ingreso_salario - $gastos_mes;

$w = function ($monto, $total) { return max(2, min(100, round(($monto / $total) * 100))); };
?>
<style>
.dash-wrap{ max-width:1200px; margin:0 auto; }
.dash-title{ font-size:28px; font-weight:700; margin:10px 0 18px; }

.grid{ display:grid; gap:16px; }
.grid-3{ grid-template-columns: repeat(3,1fr); }
.grid-2-1{ grid-template-columns: 2fr 1fr; }

.card,.kpi{ background:#0f172a; border:1px solid #1f2937; border-radius:12px; padding:16px; color:#e5e7eb; }
.kpi .muted{ color:#9ca3af; font-size:12px; }
.money{ color:#34d399; font-weight:700; font-size:22px; }
.negative{ color:#f87171; font-weight:700; font-size:22px; }

.btn{ display:inline-flex; align-items:center; gap:6px; padding:8px 12px; border-radius:8px; font-size:14px; border:1px solid #334155; color:#e5e7eb; background:#0b1220; text-decoration:none; }
.btn:hover{ background:#111827; }
.btn-primary{ background:#4f46e5; border-color:#4f46e5; color:#fff; }
.btn-primary:hover{ background:#6366f1; }

.row{ display:flex; gap:10px; flex-wrap:wrap; }
.chip{ display:inline-flex; align-items:center; gap:8px; background:#0b1220; border:1px solid #1f2937; border-radius:9999px; padding:6px 10px; font-size:13px; color:#e5e7eb; }
.muted{ color:#9ca3af; }

.bar{ height:10px; width:100%; background:#1f2937; border-radius:8px; overflow:hidden; }
.fill-blue{ background:#60a5fa; height:100%; }
.fill-violet{ background:#a78bfa; height:100%; }
.fill-green{ background:#34d399; height:100%; }
.fill-red{ background:#fb7185; height:100%; }

.section-title{ font-weight:700; margin-bottom:8px; }

/* Dona sin JS */
.donut{ position:relative; width:110px; height:110px; border-radius:9999px; background:conic-gradient(#34d399 calc(var(--p,20)*1%), #0b1220 0); }
.donut::before{ content:""; position:absolute; inset:10px; background:#0b1220; border-radius:9999px; box-shadow: inset 0 0 0 1px #111827; }
.donut b{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#34d399; font-size:18px; }

.quick-links{ display:grid; grid-template-columns: repeat(3,1fr); gap:10px; }
@media (max-width:980px){
  .grid-3,.grid-2-1{ grid-template-columns:1fr; }
  .quick-links{ grid-template-columns:1fr; }
}
</style>

<div class="dash-wrap">
  <h1 class="dash-title">Panel general</h1>
  <p class="muted" style="margin-top:-6px">
    Resumen del mes: balance, ingresos y gastos, distribución por categorías,
    ahorro planeado y accesos rápidos a reportes.
  </p>

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
        <span class="chip">✅ Cumplimiento de objetivos: <span style="color:#c4b5fd">COP <?= number_format($bono_objetivos, 0, ',', '.') ?></span></span>
      </div>
      <div class="row" style="margin-top:10px;">
        <a class="btn btn-primary" href="#">Agregar ingreso</a>
        <a class="btn" href="#">Importar CSV</a>
      </div>
    </div>

    <div class="kpi">
      <div class="muted">Gastos (mes)</div>
      <div class="negative">COP <?= number_format($gastos_mes, 0, ',', '.') ?></div>
      <div class="row" style="margin-top:10px;">
        <a class="btn btn-primary" href="#">Agregar gasto</a>
        <a class="btn" href="#">Generar desde reglas</a>
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

      <div style="margin-top:12px; display:grid; gap:12px;">
        <?php
        $colors = ['fill-blue','fill-violet','fill-green','fill-red'];
        $i=0; foreach($cat as $nombre=>$monto):
          $width = $w($monto, $total_gastos);
          $class = $colors[$i % count($colors)]; $i++;
        ?>
          <div>
            <div class="row" style="justify-content:space-between; font-size:13px;">
              <span><?= htmlspecialchars($nombre) ?></span><span class="muted">COP <?= number_format($monto,0,',','.') ?></span>
            </div>
            <div class="bar"><div class="<?= $class ?>" style="width:<?= $width ?>%"></div></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="section-title">Ahorros planeados</div>
      <div class="row" style="align-items:center;">
        <div class="donut" style="--p: <?= (int)$tasa_ahorro ?>;"><b><?= (int)$tasa_ahorro ?>%</b></div>
        <div>
          <div style="font-weight:600;">Regla: Ahorro 10% por quincena</div>
          <div class="muted">Meta: COP 8.000.000 • Estimada: 03/2025</div>
        </div>
      </div>

      <div class="row" style="margin-top:12px;">
        <span class="chip">📧 Factura Claro <span class="muted">• 20 de cada mes</span></span>
        <span class="chip">🌐 Internet <span class="muted">• 5 de cada mes</span></span>
      </div>

      <div style="margin-top:12px; border-top:1px solid #1f2937; padding-top:12px;">
        <div class="section-title">Pagos totales</div>
        <div style="display:grid; gap:6px; font-size:14px;">
          <div class="row" style="justify-content:space-between;"><span>1ª quincena</span><span>COP 500.000</span></div>
          <div class="row" style="justify-content:space-between;"><span>2ª quincena</span><span>COP 600.000</span></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Gastos mensuales (placeholder) -->
  <section class="card" style="margin-top:16px;">
    <div class="row" style="justify-content:space-between;">
      <div class="section-title">Gastos mensuales (placeholder)</div>
      <div class="muted">Ene → Sep</div>
    </div>
    <p class="muted" style="margin-top:8px;">
      Aquí irá tu gráfico real (línea). Por ahora: Ene 200k · Feb 350k · Mar 280k · Abr 300k · May 360k · Jun 330k · Jul 410k · Ago 390k · Sep 600k.
    </p>
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
