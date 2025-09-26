<?php
$titulo='Transacciones'; $pageClass='page-transactions';
require BASE_PATH.'/app/Views/layouts/header.php';

$rows = [
  ['fecha'=>'2025-09-15','categoria'=>'Sueldo','tipo'=>'income','monto'=>1500000,'desc'=>'Pago quincena'],
  ['fecha'=>'2025-09-16','categoria'=>'Comida','tipo'=>'expense','monto'=>-85000,'desc'=>'Mercado'],
  ['fecha'=>'2025-09-17','categoria'=>'Servicios','tipo'=>'expense','monto'=>-40000,'desc'=>'Internet'],
  ['fecha'=>'2025-09-18','categoria'=>'Ahorro general','tipo'=>'expense','monto'=>-120000,'desc'=>'Regla 10%'],
];
$fmt = fn($n)=> ($n<0?'- ':'')."COP ".number_format(abs($n),0,',','.');
?>
<style>
.wrap{max-width:1200px;margin:0 auto}
.card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid #1f2937;padding:8px;text-align:left}
.badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:8px;background:#111827;border:1px solid #334155}
.row{display:flex;gap:8px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;padding:8px 12px;border-radius:8px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;text-decoration:none}
.btn:hover{background:#111827}
.btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
</style>
<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Transacciones</h1>
  <div class="row" style="margin-bottom:12px">
    <a class="btn-primary btn" href="#">+ Agregar</a>
    <a class="btn" href="#">Importar CSV</a>
    <span class="badge">Filtro: Septiembre 2025</span>
    <span class="badge">Vista: Todas</span>
  </div>
  <div class="card">
    <table class="table">
      <thead><tr><th>Fecha</th><th>Categoría</th><th>Tipo</th><th>Monto</th><th>Descripción</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['fecha'])?></td>
            <td><?=htmlspecialchars($r['categoria'])?></td>
            <td><?=$r['tipo']==='income'?'Ingreso':'Gasto'?></td>
            <td style="color:<?=$r['monto']<0?'#fb7185':'#34d399'?>"><?=$fmt($r['monto'])?></td>
            <td><?=htmlspecialchars($r['desc'])?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php require BASE_PATH.'/app/Views/layouts/footer.php'; ?>
