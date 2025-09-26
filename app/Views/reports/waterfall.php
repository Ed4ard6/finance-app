<?php
$titulo='Waterfall mensual'; $pageClass='page-waterfall';
require BASE_PATH.'/app/Views/layouts/header.php';

$inicio = 1300000; // saldo inicial del mes
$items = [
  ['label'=>'Sueldo','delta'=>+1500000],
  ['label'=>'Renta','delta'=>-700000],
  ['label'=>'Comida','delta'=>-300000],
  ['label'=>'Servicios','delta'=>-180000],
  ['label'=>'Ahorro','delta'=>-200000],
];
$fin = $inicio + array_sum(array_column($items,'delta'));
$fmt = fn($n)=>"COP ".number_format($n,0,',','.');
$peak = max($inicio,$fin, ...array_map(fn($i)=>abs($i['delta']),$items));
$w = fn($v,$p)=> max(4,min(100,round((abs($v)/max(1,$p))*100)));
?>
<style>
.wrap{max-width:1000px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.bar{height:22px;border-radius:6px}
.pos{background:#34d399}
.neg{background:#fb7185}
.gray{background:#475569}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #1f2937;padding:8px;text-align:left}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Waterfall mensual</h1>
  <div class="card grid">
    <div>
      <div style="margin:6px 0">Saldo inicial</div>
      <div class="bar gray" style="width:<?=$w($inicio,$peak)?>%"></div>
      <?php foreach($items as $i): ?>
        <div style="margin:10px 0;display:flex;align-items:center;gap:10px">
          <div style="width:160px"><?=$i['label']?></div>
          <div class="bar <?=$i['delta']>=0?'pos':'neg'?>" style="width:<?=$w($i['delta'],$peak)?>%"></div>
        </div>
      <?php endforeach ?>
      <div style="margin:6px 0">Saldo final</div>
      <div class="bar gray" style="width:<?=$w($fin,$peak)?>%"></div>
    </div>
    <div>
      <table class="table">
        <thead><tr><th>Concepto</th><th>Variación</th></tr></thead>
        <tbody>
          <tr><td><b>Saldo inicial</b></td><td><?=$fmt($inicio)?></td></tr>
          <?php foreach($items as $i): ?>
            <tr>
              <td><?=$i['label']?></td>
              <td style="color:<?=$i['delta']>=0?'#34d399':'#fb7185'?>"><?=$i['delta']>=0?'+ ': '- '?><?=$fmt(abs($i['delta']))?></td>
            </tr>
          <?php endforeach ?>
          <tr><td><b>Saldo final</b></td><td><?=$fmt($fin)?></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
