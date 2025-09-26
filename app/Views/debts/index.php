<?php
$titulo='Deudas'; $pageClass='page-debts';
require BASE_PATH.'/app/Views/layouts/header.php';
$debts=[
  ['name'=>'Cuota auto','saldo'=>9200000,'tasa'=>0.165,'min'=>520000,'dia'=>10],
  ['name'=>'Préstamo personal','saldo'=>7500000,'tasa'=>0.210,'min'=>190000,'dia'=>25],
  ['name'=>'Tarjeta crédito','saldo'=>3400000,'tasa'=>0.320,'min'=>150000,'dia'=>5],
];
$fmt = fn($n)=>"COP ".number_format($n,0,',','.');
?>
<style>
.wrap{max-width:1000px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #1f2937;padding:8px;text-align:left}
.btn{display:inline-flex;align-items:center;padding:6px 10px;border:1px solid #334155;border-radius:8px;background:#0b1220;color:#e5e7eb;text-decoration:none}
.btn:hover{background:#111827}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Deudas</h1>
  <div class="card">
    <div style="margin-bottom:10px"><a class="btn" href="/debts/compare">Comparar estrategias ▸</a></div>
    <table class="table">
      <thead><tr><th>Deuda</th><th>Saldo</th><th>Tasa EA</th><th>Pago mínimo</th><th>Día de pago</th></tr></thead>
      <tbody>
        <?php foreach($debts as $d): ?>
          <tr>
            <td><?=htmlspecialchars($d['name'])?></td>
            <td><?=$fmt($d['saldo'])?></td>
            <td><?=number_format($d['tasa']*100,2,',','.')?>%</td>
            <td><?=$fmt($d['min'])?></td>
            <td><?=$d['dia']?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
