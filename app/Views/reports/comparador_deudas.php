<?php
// ===== Layout =====
$titulo    = 'Comparador de pago de deudas (Snowball vs Avalanche)';
$pageClass = 'page-debt-compare';
require BASE_PATH . '/app/Views/layouts/header.php';

/* ============================================================
   MOCK DATA (cámbialo cuando integres BD)
   balance y pagos en COP; tasas EA (Efectivo Anual)
   extra_budget: monto adicional mensual que pones encima del mínimo
   Estrategias:
     - snowball: ordenar por saldo (asc)
     - avalanche: ordenar por tasa (desc)
   ============================================================ */
$debts = [
  ['name'=>'Cuota auto',        'balance'=>9200000, 'annual_rate'=>0.165, 'min_payment'=>520000],
  ['name'=>'Préstamo personal', 'balance'=>7500000, 'annual_rate'=>0.210, 'min_payment'=>190000],
  ['name'=>'Tarjeta crédito',   'balance'=>3400000, 'annual_rate'=>0.320, 'min_payment'=>150000],
];
$extra_budget = 200000; // extra que aportarás cada mes (además de mínimos)

// Utilidades
function ea_to_im($ea){ return pow(1+$ea, 1/12) - 1; }     // EA -> interés mensual
function months_to_date(int $m){ $dt = new DateTime('first day of this month'); $dt->modify("+$m month"); return $dt->format('M Y'); }

// Simulador: devuelve [months, total_interest, timeline[]]
function simulate(array $debts, string $strategy, int $extra_budget): array {
  // copiar y normalizar
  $items = [];
  foreach ($debts as $d){
    $items[] = [
      'name' => $d['name'],
      'bal'  => (float)$d['balance'],
      'i'    => ea_to_im((float)$d['annual_rate']),
      'min'  => (float)$d['min_payment'],
    ];
  }
  $months = 0; $total_interest = 0.0; $timeline = [];

  // seguridad para evitar loops infinitos
  for ($guard=0; $guard<600; $guard++){
    // ¿todas pagadas?
    $actives = array_filter($items, fn($x)=>$x['bal'] > 1);
    if (!$actives) break;

    // ordenar según estrategia
    if ($strategy==='snowball'){
      usort($items, fn($a,$b)=> $a['bal'] <=> $b['bal']);
    } else { // avalanche
      usort($items, fn($a,$b)=> $b['i']   <=>  $a['i']);
    }

    // intereses del mes
    $inter_m = 0.0;
    foreach ($items as &$d){
      if ($d['bal'] <= 1) continue;
      $int = $d['bal'] * $d['i'];
      $d['bal'] += $int;
      $inter_m += $int;
    } unset($d);
    $total_interest += $inter_m;

    // pagar mínimos
    $surplus = $extra_budget;
    foreach ($items as &$d){
      if ($d['bal'] <= 1) continue;
      $pay = min($d['min'], $d['bal']);
      $d['bal'] -= $pay;
      if ($d['bal'] < 0) $d['bal'] = 0;
    } unset($d);

    // dirigir el extra a la deuda objetivo (la primera según el orden actual)
    foreach ($items as &$d){
      if ($d['bal'] <= 1) continue;
      $target =& $d;
      break;
    } unset($d);
    if (isset($target)){
      $extra = min($surplus, $target['bal']);
      $target['bal'] -= $extra;
    }

    // registro de timeline (saldo total)
    $sum_bal = array_reduce($items, fn($c,$x)=>$c+$x['bal'], 0.0);
    $timeline[] = ['month'=>$months, 'total_balance'=>$sum_bal, 'interest'=>$inter_m];
    $months++;
  }

  return ['months'=>$months, 'total_interest'=>$total_interest, 'timeline'=>$timeline, 'debts'=>$items];
}

// seleccionar estrategia via query (?s=snowball|avalanche)
$sel = $_GET['s'] ?? 'snowball';
if (!in_array($sel, ['snowball','avalanche'])) $sel = 'snowball';

$rSnow = simulate($debts, 'snowball',  $extra_budget);
$rAval = simulate($debts, 'avalanche', $extra_budget);

// KPIs
$freeSnow = months_to_date($rSnow['months']);
$freeAval = months_to_date($rAval['months']);
$intSnow  = round($rSnow['total_interest']);
$intAval  = round($rAval['total_interest']);
$ahorroIntereses = $intSnow - $intAval; // si es +, avalanche ahorra
$recom = $ahorroIntereses > 0 ? 'Avalanche ahorra más intereses.' : ($ahorroIntereses < 0 ? 'Snowball ahorra más intereses.' : 'Ambas estrategias empatan en intereses.');

// formateo rápido
$fmt = fn($n)=> 'COP '.number_format($n,0,',','.');
?>
<style>
.wrap { max-width:1200px; margin:0 auto; }
.h1 { font-size:28px; font-weight:700; margin:12px 0 16px; }
.tabs { display:flex; gap:8px; margin:8px 0 12px; }
.tab { border:1px solid #334155; background:#0b1220; color:#e5e7eb; padding:8px 12px; border-radius:8px; text-decoration:none; }
.tab.active { background:#4f46e5; border-color:#4f46e5; color:#fff; }
.grid { display:grid; gap:16px; }
.grid-3 { grid-template-columns: repeat(3,1fr); }
.card { background:#0f172a; border:1px solid #1f2937; border-radius:12px; padding:16px; color:#e5e7eb; }
.kpi { background:#0f172a; border:1px solid #1f2937; border-radius:12px; padding:16px; color:#e5e7eb; }
.kpi .label{ color:#9ca3af; font-size:12px; }
.kpi .val{ font-weight:700; font-size:22px; }
.list { width:100%; border-collapse:collapse; }
.list th, .list td { border-bottom:1px solid #1f2937; padding:8px; text-align:left; }
.badge { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; background:#111827; border:1px solid #334155; font-size:13px; }
.row { display:flex; justify-content:space-between; gap:10px; }
.positive{ color:#34d399; } .negative{ color:#fb7185; }
@media(max-width:980px){ .grid-3{ grid-template-columns:1fr; } }
</style>

<div class="wrap">
  <h1 class="h1">Comparar estrategias de pago de deudas</h1>

  <!-- Tabs por query -->
  <div class="tabs">
    <a class="tab <?= $sel==='snowball'?'active':'' ?>"  href="?s=snowball">Snowball</a>
    <a class="tab <?= $sel==='avalanche'?'active':'' ?>" href="?s=avalanche">Avalanche</a>
  </div>

  <!-- KPIs (comparativo) -->
  <section class="grid grid-3">
    <div class="kpi">
      <div class="label">Fecha de libertad (Snowball)</div>
      <div class="val"><?= htmlspecialchars($freeSnow) ?></div>
    </div>
    <div class="kpi">
      <div class="label">Fecha de libertad (Avalanche)</div>
      <div class="val"><?= htmlspecialchars($freeAval) ?></div>
    </div>
    <div class="kpi">
      <div class="label">Ahorro en intereses (Avalanche vs Snowball)</div>
      <div class="val <?= $ahorroIntereses>0?'positive':($ahorroIntereses<0?'negative':'') ?>">
        <?= $fmt(abs($ahorroIntereses)) ?> <?= $ahorroIntereses===0?'':'('.($ahorroIntereses>0?'+':'-').')' ?>
      </div>
    </div>
  </section>

  <section class="card" style="margin-top:16px;">
    <div class="row">
      <div class="badge">💡 Recomendación</div>
      <div class="badge">Extra mensual: <?= $fmt($extra_budget) ?></div>
    </div>
    <p style="margin-top:10px; color:#cbd5e1;"><?= $recom ?></p>
  </section>

  <!-- Tab contenido: tabla de deudas + totales de intereses -->
  <section class="grid" style="margin-top:16px;">
    <div class="card">
      <h3 style="margin:0 0 10px 0;">Deudas (datos base)</h3>
      <table class="list">
        <thead>
          <tr><th>Deuda</th><th>Saldo</th><th>Tasa EA</th><th>Pago mínimo</th></tr>
        </thead>
        <tbody>
          <?php foreach ($debts as $d): ?>
            <tr>
              <td><?= htmlspecialchars($d['name']) ?></td>
              <td><?= $fmt($d['balance']) ?></td>
              <td><?= number_format($d['annual_rate']*100,2,',','.') ?>%</td>
              <td><?= $fmt($d['min_payment']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card">
      <h3 style="margin:0 0 10px 0;">Resumen por estrategia</h3>
      <table class="list">
        <thead>
          <tr><th>Estrategia</th><th>Meses</th><th>Fecha fin</th><th>Intereses totales</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>Snowball</td>
            <td><?= (int)$rSnow['months'] ?></td>
            <td><?= htmlspecialchars($freeSnow) ?></td>
            <td><?= $fmt($intSnow) ?></td>
          </tr>
          <tr>
            <td>Avalanche</td>
            <td><?= (int)$rAval['months'] ?></td>
            <td><?= htmlspecialchars($freeAval) ?></td>
            <td><?= $fmt($intAval) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <!-- Nota: aquí podrías dibujar una “línea” textual o barras con CSS si quieres ver el timeline -->
  <section class="card" style="margin-top:16px;">
    <h3 style="margin:0 0 10px 0;">Timeline (saldo total mensual)</h3>
    <p class="badge" style="margin-bottom:8px;">Vista: <?= $sel==='snowball'?'Snowball':'Avalanche' ?></p>
    <div style="display:grid; gap:6px;">
      <?php
        $use = $sel==='snowball' ? $rSnow['timeline'] : $rAval['timeline'];
        $peak = max(array_column($use,'total_balance')) ?: 1;
        foreach ($use as $row):
          $w = max(2, round(($row['total_balance']/$peak)*100));
      ?>
        <div style="display:flex; align-items:center; gap:8px;">
          <div style="width:70px;color:#9ca3af;">m<?= $row['month'] ?></div>
          <div style="flex:1; height:10px; border-radius:8px; overflow:hidden; background:#1f2937;">
            <div style="width:<?= $w ?>%; height:100%; background:#60a5fa;"></div>
          </div>
          <div style="width:160px; text-align:right; color:#9ca3af;">
            <?= $fmt(round($row['total_balance'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<?php require BASE_PATH . '/app/Views/layouts/footer.php'; ?>
