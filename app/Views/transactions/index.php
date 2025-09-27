<?php
// Espera: rows[], ym, view, mesBonito, sumIncome, sumExpense, from, to
//         cats[kind][] (id,name), catSel[kind] (array de ids)
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');
$titulo  = $titulo ?? 'Transacciones';
$pageClass = $pageClass ?? 'page-transactions';

// ===== Helpers locales (sin helpers globales) =====
function fmt_money_abs($n){ return 'COP ' . number_format(abs((float)$n), 0, ',', '.'); }
/** Devuelve [texto, claseColor] usando el tipo de categoría */
function fmt_money_signed($amount, $kind){
  $isIncome = ($kind === 'income');
  $class = $isIncome ? 'money--pos' : 'money--neg';
  $sign  = $isIncome ? '' : '- ';
  return [$sign . fmt_money_abs($amount), $class];
}
?>
<style>
/* ---------- Layout ---------- */
.page-transactions .wrap{max-width:1100px;margin:0 auto;padding:0 8px}
.page-transactions h1{font-size:28px;margin:14px 0 10px;color:#e5e7eb}

.header-bar{display:grid;grid-template-columns:1fr auto;align-items:center;gap:10px;margin-bottom:12px}
.header-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end}
@media (max-width:640px){
  .header-bar{grid-template-columns:1fr}
  .header-actions{justify-content:flex-start}
}

/* ---------- Chips / Botones ---------- */
.btn, .chip{
  display:inline-flex;align-items:center;justify-content:center;
  padding:8px 12px;border-radius:12px;border:1px solid #334155;
  background:#0b1220;color:#e5e7eb;text-decoration:none;font-size:14px
}
.btn:hover{background:#111827}
.btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
.stat{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:#111827;border:1px solid #334155;color:#cbd5e1}
.stat b{color:#fff}

/* ---------- Tabla desktop ---------- */
.table-card{background:#0f172a;border:1px solid #1f2937;border-radius:14px;overflow:hidden}
table{width:100%;border-collapse:separate;border-spacing:0}
thead th{
  text-align:left;font-weight:600;color:#cbd5e1;background:#0f172a;border-bottom:1px solid rgba(148,163,184,.18);
  padding:12px 14px
}
tbody td{padding:14px;border-bottom:1px solid rgba(148,163,184,.08)}
tbody tr:nth-child(even){background:#0c1527}
tbody tr:last-child td{border-bottom:0}
tbody td.money--neg{color:#ef4444}
tbody td.money--pos{color:#22c55e}
td.actions{white-space:nowrap}
.btn.small{padding:6px 10px;border-radius:10px;font-size:13px}

/* ---------- Cards mobile ---------- */
@media (max-width:900px){
  .table-card{display:none}
  .mobile-list{display:flex;flex-direction:column;gap:12px}
  .mcard{
    background:#0f172a;border:1px solid #1f2937;border-radius:14px;padding:12px;color:#e5e7eb
  }
  .mrow{display:grid;grid-template-columns:110px 1fr;gap:8px;margin:6px 0}
  .mlabel{color:#93a5be}
  .mactions{display:flex;gap:8px;justify-content:flex-end;margin-top:8px}
  .money--neg{color:#ef4444}
  .money--pos{color:#22c55e}
}
@media (min-width:901px){ .mobile-list{display:none} }

/* ---------- Modal filtros ---------- */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(2px);display:none;z-index:60}
.modal{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);
  width:min(980px,92vw);max-height:82vh;display:none;z-index:61}
.modal .card{background:#0f172a;border:1px solid #1f2937;border-radius:14px;color:#e5e7eb}
.modal .head{position:sticky;top:0;padding:14px 16px;border-bottom:1px solid #1f2937;background:#0f172a;border-radius:14px 14px 0 0;display:flex;justify-content:space-between;align-items:center}
.modal .body{padding:14px 16px;overflow:auto;max-height:calc(82vh - 120px)}
.modal .foot{padding:12px 16px;border-top:1px solid #1f2937;display:flex;justify-content:flex-end;gap:8px}
.modal h3{margin:0;font-size:18px}
.closex{border:none;background:transparent;color:#cbd5e1;font-size:22px;cursor:pointer}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media (max-width:700px){ .grid2{grid-template-columns:1fr} }
.field label{display:block;color:#c7d2fe;margin-bottom:6px;font-size:14px} /* texto más claro */

/* ---------- Inputs bonitos ---------- */
.select, .input{
  width:100%;padding:10px 14px;border-radius:12px;border:1px solid #334155;background:#0b1220;color:#e5e7eb
}
.select{
  appearance:none;-webkit-appearance:none;-moz-appearance:none;
  background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M6 8l4 4 4-4" stroke="%23cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
  background-repeat:no-repeat;background-position:right 10px center;padding-right:36px;
}

/* Mes rápido */
.monthbox{position:relative}
.monthbox .ic{position:absolute;right:10px;top:50%;transform:translateY(-50%);opacity:.9;cursor:pointer}
/* Hacer el input completo clickable */
.monthbox input#f-ym{cursor:pointer}

/* Rango */
.quick-row{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px}
.quick{
  padding:6px 12px;border-radius:999px;border:1px solid #334155;background:#0b1220;
  color:#e5e7eb
}
.quick:hover{background:#111827;border-color:#475569}
.rangerow{display:flex;gap:10px}
@media (max-width:480px){ .rangerow{flex-direction:column} }

/* Date inputs con icono */
.datebox{position:relative}
.datebox .ic{position:absolute;right:10px;top:50%;transform:translateY(-50%);opacity:.9;pointer-events:none}

/* ---------- Categorías (dos columnas) ---------- */
.catpanel{border:1px solid #1f2937;border-radius:12px;padding:12px;min-height:220px}
.cat-head{display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap}
.cat-head .chip{padding:4px 10px;border-radius:999px;font-size:12px}
.cat-scroll{max-height:260px;overflow:auto;padding-right:6px}
.cat-group{margin-bottom:14px}
.cat-title{font-weight:700;margin:2px 0 4px;color:#e5e7eb}

.ck-grid{
  display:grid;
  grid-template-columns:minmax(180px,1fr) minmax(180px,1fr);
  column-gap:36px;
  row-gap:10px;
  align-items:center;
}
@media (max-width:640px){
  .ck-grid{grid-template-columns:1fr}
}

.ck{display:flex;align-items:center;gap:8px}
.ck input{
  width:16px;height:16px;
  accent-color:#6366f1;
}
.ck span{color:#e5e7eb}
.ck-empty{grid-column:1 / -1;color:#93a5be;padding:4px 0}

/* ---------- Datepicker (días) ---------- */
.dp{position:absolute;z-index:9999;background:#0b1220;border:1px solid #334155;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.35);width:290px;color:#e5e7eb;display:none}
.dp .dph{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-bottom:1px solid #1f2937}
.dp .dph b{font-weight:600}
.dp .nav{display:flex;gap:6px}
.dp .nav button{background:#111827;border:1px solid #334155;border-radius:10px;padding:6px 8px;color:#e5e7eb;cursor:pointer}
.dp .grid{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;padding:10px}
.dp .dow{font-size:12px;color:#93a5be;text-align:center}
.dp .day{padding:8px 0;text-align:center;border-radius:10px;cursor:pointer;background:#0f172a;border:1px solid #1f2937}
.dp .day:hover{background:#111827}
.dp .day.off{opacity:.45}
.dp .day.sel{outline:2px solid #6366f1}
.dp .day.today{box-shadow:0 0 0 2px #334155 inset}
.dp .foot{padding:8px 10px;border-top:1px solid #1f2937;text-align:right}
.dp .foot button{background:#4f46e5;border:none;border-radius:10px;padding:6px 10px;color:#fff;cursor:pointer}

/* ---------- MonthPicker (mes rápido) ---------- */
.mp{position:absolute;z-index:9999;background:#0b1220;border:1px solid #334155;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.35);width:284px;color:#e5e7eb;display:none}
.mp .mph{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-bottom:1px solid #1f2937}
.mp .mph b{font-weight:600}
.mp .nav{display:flex;gap:6px}
.mp .nav button{background:#111827;border:1px solid #334155;border-radius:10px;padding:6px 8px;color:#e5e7eb;cursor:pointer}
.mp .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px}
.mp .m{padding:10px 0;text-align:center;border-radius:10px;cursor:pointer;background:#0f172a;border:1px solid #1f2937}
.mp .m:hover{background:#111827}
.mp .foot{padding:8px 10px;border-top:1px solid #1f2937;text-align:right}
.mp .foot button{background:#4f46e5;border:none;border-radius:10px;padding:6px 10px;color:#fff;cursor:pointer}
</style>

<div class="wrap">
  <div class="header-bar">
    <h1>Transacciones</h1>
    <div class="header-actions">
      <a class="btn" href="<?= $baseUrl ?>/transactions/create">+ Agregar</a>
      <a class="btn" href="<?= $baseUrl ?>/categories">Categorías</a>
      <button class="btn" id="btn-filters">Filtros</button>
    </div>
  </div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
    <span class="stat">Ingresos: <b><?= fmt_money_abs($sumIncome) ?></b></span>
    <span class="stat">Gastos: <b><?= fmt_money_abs(abs($sumExpense)) ?></b></span>
  </div>

  <!-- Tabla desktop -->
  <div class="table-card">
    <table>
      <thead>
        <tr>
          <th style="width:130px">Fecha</th>
          <th style="width:220px">Categoría</th>
          <th style="width:110px">Tipo</th>
          <th style="width:150px">Monto</th>
          <th>Descripción</th>
          <th style="width:150px" class="actions">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <?php [$moneyText,$moneyClass] = fmt_money_signed($r['amount'] ?? 0, $r['category_kind'] ?? ''); ?>
          <tr>
            <td><?= htmlspecialchars($r['date_at']) ?></td>
            <td><?= htmlspecialchars($r['category_name'] ?? '—') ?></td>
            <td><?= ucfirst(htmlspecialchars($r['category_kind'] ?? '')) ?></td>
            <td class="<?= $moneyClass ?>"><?= $moneyText ?></td>
            <td><?= htmlspecialchars($r['description'] ?? '') ?></td>
            <td class="actions">
              <a class="btn small" href="<?= $baseUrl ?>/transactions/edit?id=<?= (int)$r['id'] ?>">Editar</a>
              <form action="<?= $baseUrl ?>/transactions/delete" method="post" style="display:inline" onsubmit="return confirm('¿Eliminar transacción?');">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn small" type="submit">Borrar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; if (!$rows): ?>
          <tr><td colspan="6" style="color:#93a5be">Sin transacciones para el filtro actual.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Cards mobile -->
  <div class="mobile-list">
    <?php foreach($rows as $r): ?>
      <?php [$moneyText,$moneyClass] = fmt_money_signed($r['amount'] ?? 0, $r['category_kind'] ?? ''); ?>
      <div class="mcard">
        <div class="mrow"><div class="mlabel">Fecha</div><div><?= htmlspecialchars($r['date_at']) ?></div></div>
        <div class="mrow"><div class="mlabel">Categoría</div><div><?= htmlspecialchars($r['category_name'] ?? '—') ?></div></div>
        <div class="mrow"><div class="mlabel">Tipo</div><div><?= ucfirst(htmlspecialchars($r['category_kind'] ?? '')) ?></div></div>
        <div class="mrow"><div class="mlabel">Monto</div><div class="<?= $moneyClass ?>"><?= $moneyText ?></div></div>
        <?php if (!empty($r['description'])): ?>
          <div class="mrow"><div class="mlabel">Descripción</div><div><?= htmlspecialchars($r['description']) ?></div></div>
        <?php endif; ?>
        <div class="mactions">
          <a class="btn small" href="<?= $baseUrl ?>/transactions/edit?id=<?= (int)$r['id'] ?>">Editar</a>
          <form action="<?= $baseUrl ?>/transactions/delete" method="post" onsubmit="return confirm('¿Eliminar transacción?');">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn small" type="submit">Borrar</button>
          </form>
        </div>
      </div>
    <?php endforeach; if (!$rows): ?>
      <div class="mcard" style="color:#93a5be">Sin transacciones para el filtro actual.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal de filtros -->
<div class="modal-backdrop" id="mb"></div>
<div class="modal" id="mf">
  <div class="card">
    <div class="head">
      <h3>Filtros</h3>
      <button class="closex" id="mx" aria-label="Cerrar">×</button>
    </div>
    <div class="body">
      <div class="grid2">
        <!-- Columna izquierda -->
        <div>
          <div class="field">
            <label>Tipo</label>
            <select class="select" id="f-type">
              <option value="all"     <?= $view==='all'?'selected':'' ?>>Todas</option>
              <option value="expense" <?= $view==='expense'?'selected':'' ?>>Solo egresos</option>
              <option value="income"  <?= $view==='income'?'selected':'' ?>>Solo ingresos</option>
            </select>
          </div>

          <div class="field">
            <label>Mes (rápido)</label>
            <div class="monthbox">
              <input class="input" id="f-ym" type="text" inputmode="none" readonly value="<?= htmlspecialchars($ym) ?>" />
              <span class="ic" id="ym-ic" title="Elegir mes">📅</span>
            </div>
          </div>

          <div class="field">
            <label>Rango de fechas (opcional)</label>
            <div class="quick-row">
              <button type="button" class="quick" data-q="this">Este mes</button>
              <button type="button" class="quick" data-q="prevmonth">Mes pasado</button>
              <button type="button" class="quick" data-q="7">Últimos 7 días</button>
              <button type="button" class="quick" data-q="30">Últimos 30 días</button>
              <button type="button" class="quick" data-q="year">Año actual</button>
            </div>
            <div class="rangerow">
              <div class="datebox" style="flex:1">
                <input class="input dp-bind" id="f-from" placeholder="dd/mm/aaaa" value="<?= htmlspecialchars($from) ?>">
                <span class="ic">📅</span>
              </div>
              <div class="datebox" style="flex:1">
                <input class="input dp-bind" id="f-to"   placeholder="dd/mm/aaaa" value="<?= htmlspecialchars($to) ?>">
                <span class="ic">📅</span>
              </div>
            </div>
            <div style="color:#93a5be;margin-top:6px;font-size:13px">Si completas el rango, se ignora el mes.</div>
          </div>
        </div>

        <!-- Columna derecha: categorías (2 columnas) -->
        <div>
          <div class="field">
            <label>Categorías</label>
            <div class="catpanel">
              <div class="cat-head">
                <button class="chip" id="cat-all">Todas</button>
                <button class="chip" id="cat-none">Ninguna</button>
              </div>

              <div class="cat-scroll">
                <?php
                  $groups = [
                    'expense'=>'Gastos',
                    'debt'   =>'Deudas',
                    'saving' =>'Ahorros',
                    'income' =>'Ingresos',
                  ];
                  foreach ($groups as $k=>$label):
                    $list = $cats[$k] ?? [];
                    $sel  = $catSel[$k] ?? [];
                ?>
                  <div class="cat-group" data-kind="<?= $k ?>">
                    <div class="cat-head" style="margin-bottom:6px">
                      <div class="cat-title"><?= $label ?></div>
                      <button class="chip" data-scope="<?= $k ?>" data-act="all">Todas</button>
                      <button class="chip" data-scope="<?= $k ?>" data-act="none">Ninguna</button>
                    </div>

                    <div class="ck-grid">
                      <?php if ($list): foreach ($list as $c): ?>
                        <label class="ck">
                          <input type="checkbox" class="ckb" data-kind="<?= $k ?>" value="<?= (int)$c['id'] ?>"
                            <?= in_array((int)$c['id'], $sel, true)?'checked':'' ?>>
                          <span><?= htmlspecialchars($c['name']) ?></span>
                        </label>
                      <?php endforeach; else: ?>
                        <div class="ck-empty">— Sin categorías —</div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div> <!-- grid2 -->
    </div>
    <div class="foot">
      <button class="btn" id="f-clear">Limpiar</button>
      <button class="btn" id="f-cancel">Cancelar</button>
      <button class="btn btn-primary" id="f-apply">Aplicar</button>
    </div>
  </div>
</div>

<!-- Datepicker (plantilla) -->
<div class="dp" id="datepicker">
  <div class="dph">
    <div class="nav">
      <button type="button" data-dp="prev">◀</button>
      <button type="button" data-dp="today">Hoy</button>
      <button type="button" data-dp="next">▶</button>
    </div>
    <b id="dp-title">septiembre de 2025</b>
  </div>
  <div class="grid" id="dp-grid"></div>
  <div class="foot"><button type="button" data-dp="ok">Elegir</button></div>
</div>

<!-- MonthPicker (para Mes rápido) -->
<div class="mp" id="monthpicker">
  <div class="mph">
    <div class="nav">
      <button type="button" data-mp="prevY">◀</button>
      <button type="button" data-mp="thisY">Este año</button>
      <button type="button" data-mp="nextY">▶</button>
    </div>
    <b id="mp-title">2025</b>
  </div>
  <div class="grid" id="mp-grid"></div>
  <div class="foot"><button type="button" data-mp="ok">Elegir</button></div>
</div>

<script>
// ---------- Modal open/close ----------
const mb = document.getElementById('mb');
const mf = document.getElementById('mf');
function openModal(){ mb.style.display='block'; mf.style.display='block'; }
function closeModal(){ mb.style.display='none'; mf.style.display='none'; hideDP(); hideMP(); }
document.getElementById('btn-filters').addEventListener('click', openModal);
document.getElementById('mx').addEventListener('click', closeModal);
document.getElementById('f-cancel').addEventListener('click', closeModal);
mb.addEventListener('click', closeModal);

// ---------- Quick range ----------
const fromI = document.getElementById('f-from');
const toI   = document.getElementById('f-to');
document.querySelectorAll('.quick').forEach(b=>{
  b.addEventListener('click', ()=>{
    const q = b.dataset.q;
    const today = new Date();
    const fmt = d => `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
    if(q==='7' || q==='30'){
      const d2=new Date(today), d1=new Date(today);
      d1.setDate(d1.getDate() - (q==='7'?7:30));
      fromI.value = fmt(d1); toI.value = fmt(d2);
    } else if (q==='this'){
      const d1=new Date(today.getFullYear(), today.getMonth(), 1);
      const d2=new Date(today.getFullYear(), today.getMonth()+1, 0);
      fromI.value = fmt(d1); toI.value = fmt(d2);
    } else if (q==='prevmonth'){
      const d1=new Date(today.getFullYear(), today.getMonth()-1, 1);
      const d2=new Date(today.getFullYear(), today.getMonth(), 0);
      fromI.value = fmt(d1); toI.value = fmt(d2);
    } else if (q==='year'){
      const d1=new Date(today.getFullYear(),0,1);
      const d2=new Date(today.getFullYear(),11,31);
      fromI.value = fmt(d1); toI.value = fmt(d2);
    }
  });
});

// ---------- Categorías: toggles ----------
function setGroup(scope, all){
  document.querySelectorAll(`.cat-group[data-kind="${scope}"] .ckb`).forEach(ch=>{ ch.checked = !!all; });
}
document.querySelectorAll('.cat-head .chip[data-act]').forEach(chip=>{
  chip.addEventListener('click', ()=> setGroup(chip.dataset.scope, chip.dataset.act==='all'));
});
document.getElementById('cat-all').addEventListener('click', ()=> document.querySelectorAll('.ckb').forEach(ch=>ch.checked=true));
document.getElementById('cat-none').addEventListener('click', ()=> document.querySelectorAll('.ckb').forEach(ch=>ch.checked=false));

// ---------- Limpiar ----------
document.getElementById('f-clear').addEventListener('click', ()=>{
  document.getElementById('f-type').value = 'all';
  document.getElementById('f-ym').value   = '<?= htmlspecialchars($ym) ?>';
  fromI.value=''; toI.value='';
  document.querySelectorAll('.ckb').forEach(ch=>ch.checked=false);
});

// ---------- Aplicar ----------
document.getElementById('f-apply').addEventListener('click', ()=>{
  const type = document.getElementById('f-type').value;
  const ym   = document.getElementById('f-ym').value; // yyyy-mm

  const params = new URLSearchParams();
  if (type && type!=='all') params.set('view', type);
  if (ym) params.set('ym', ym);

  const from = fromI.value.trim(), to = toI.value.trim();
  if (from && to){ params.set('from', from); params.set('to', to); }

  const csv = kind => Array.from(document.querySelectorAll(`.ckb[data-kind="${kind}"]:checked`)).map(x=>x.value).join(',');
  const ce=csv('expense'), cd=csv('debt'), cs=csv('saving'), ci=csv('income');
  if (ce) params.set('cat_expense', ce);
  if (cd) params.set('cat_debt',    cd);
  if (cs) params.set('cat_saving',  cs);
  if (ci) params.set('cat_income',  ci);

  location.href = '<?= $baseUrl ?>/transactions?' + params.toString();
});

// accesibilidad
document.getElementById('btn-filters').addEventListener('keyup',(e)=>{ if(e.key==='Enter') openModal(); });

/* ================== Datepicker (días) ================== */
const dp = document.getElementById('datepicker');
const dpTitle = document.getElementById('dp-title');
const dpGrid  = document.getElementById('dp-grid');
let dpBindInput = null, dpMonth = new Date();

function showDP(input){
  dpBindInput = input;
  const r = input.getBoundingClientRect();
  dp.style.left = (window.scrollX + r.left) + 'px';
  dp.style.top  = (window.scrollY + r.bottom + 6) + 'px';
  dp.style.display='block';
  const val = input.value.trim();
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)){
    const [dd,mm,yy] = val.split('/').map(n=>parseInt(n,10));
    dpMonth = new Date(yy, mm-1, 1);
  }else{
    dpMonth = new Date();
  }
  renderDP();
}
function hideDP(){ dp.style.display='none'; dpBindInput=null; }

function monthName(d){
  return d.toLocaleDateString('es-CO',{month:'long', year:'numeric'}).replace(/^\w/, c=>c.toUpperCase());
}
function renderDP(){
  dpTitle.textContent = monthName(dpMonth);
  dpGrid.innerHTML = '';
  const dows = ['L','M','X','J','V','S','D'];
  dows.forEach(w=>{
    const el = document.createElement('div'); el.className='dow'; el.textContent=w; dpGrid.appendChild(el);
  });
  const first = new Date(dpMonth.getFullYear(), dpMonth.getMonth(), 1);
  const startIdx = (first.getDay()+6)%7; // lunes=0
  const daysInMonth = new Date(dpMonth.getFullYear(), dpMonth.getMonth()+1, 0).getDate();
  const prevMonthDays = new Date(dpMonth.getFullYear(), dpMonth.getMonth(), 0).getDate();

  for(let i=0;i<startIdx;i++){
    const dd = prevMonthDays - startIdx + i + 1;
    const cell = mkDay(dd, true, new Date(dpMonth.getFullYear(), dpMonth.getMonth()-1, dd));
    dpGrid.appendChild(cell);
  }
  for(let d=1; d<=daysInMonth; d++){
    const cell = mkDay(d, false, new Date(dpMonth.getFullYear(), dpMonth.getMonth(), d));
    dpGrid.appendChild(cell);
  }
  const totalCells = 7 + startIdx + daysInMonth;
  const extra = (totalCells%7===0) ? 0 : (7 - (totalCells%7));
  for(let d=1; d<=extra; d++){
    const cell = mkDay(d, true, new Date(dpMonth.getFullYear(), dpMonth.getMonth()+1, d));
    dpGrid.appendChild(cell);
  }
}
function mkDay(d, off, dateObj){
  const el = document.createElement('div');
  el.className = 'day' + (off?' off':'');
  const today = new Date();
  if (dateObj.toDateString() === new Date(today.getFullYear(),today.getMonth(),today.getDate()).toDateString()){
    el.classList.add('today');
  }
  el.textContent = d;
  el.addEventListener('click', ()=>{
    dpGrid.querySelectorAll('.day').forEach(x=>x.classList.remove('sel'));
    el.classList.add('sel');
    const dd=String(dateObj.getDate()).padStart(2,'0'), mm=String(dateObj.getMonth()+1).padStart(2,'0'), yy=dateObj.getFullYear();
    if (dpBindInput) dpBindInput.value = `${dd}/${mm}/${yy}`;
  });
  return el;
}
dp.addEventListener('click', e=>{
  const act = e.target.getAttribute('data-dp');
  if (!act) return;
  if (act==='prev') { dpMonth = new Date(dpMonth.getFullYear(), dpMonth.getMonth()-1, 1); renderDP(); }
  if (act==='next') { dpMonth = new Date(dpMonth.getFullYear(), dpMonth.getMonth()+1, 1); renderDP(); }
  if (act==='today'){ dpMonth = new Date(); renderDP(); }
  if (act==='ok')   { hideDP(); }
});
document.querySelectorAll('.dp-bind').forEach(inp=>{
  inp.addEventListener('focus', ()=>showDP(inp));
  inp.addEventListener('click', ()=>showDP(inp));
});
document.addEventListener('click', (e)=>{
  if (!dp.contains(e.target) && !e.target.classList.contains('dp-bind') && !e.target.closest('.datebox')) {
    if (dp.style.display==='block') hideDP();
  }
});

/* ================== MonthPicker (mes rápido) ================== */
const mp = document.getElementById('monthpicker');
const mpTitle = document.getElementById('mp-title');
const mpGrid  = document.getElementById('mp-grid');
const ymInput = document.getElementById('f-ym');
const ymIc    = document.getElementById('ym-ic');
let mpYear = new Date().getFullYear();
let mpSel = null; // {y, m}

function showMP(anchor){
  const r = anchor.getBoundingClientRect();
  mp.style.left = (window.scrollX + r.left) + 'px';
  mp.style.top  = (window.scrollY + r.bottom + 6) + 'px';
  const val = ymInput.value; // yyyy-mm
  if (/^\d{4}-\d{2}$/.test(val)){
    const [y,m] = val.split('-').map(n=>parseInt(n,10));
    mpYear = y; mpSel = {y:y,m:m-1};
  }else{
    const t = new Date(); mpYear = t.getFullYear(); mpSel = {y:mpYear, m:t.getMonth()};
  }
  renderMP(); mp.style.display='block';
}
function hideMP(){ mp.style.display='none'; }

function renderMP(){
  mpTitle.textContent = mpYear;
  mpGrid.innerHTML='';
  const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
  months.forEach((name,idx)=>{
    const b = document.createElement('div');
    b.className='m';
    b.textContent = name;
    if (mpSel && mpSel.y===mpYear && mpSel.m===idx) b.style.outline='2px solid #6366f1';
    b.addEventListener('click', ()=>{
      mpSel = {y: mpYear, m: idx};
      ymInput.value = `${mpSel.y}-${String(mpSel.m+1).padStart(2,'0')}`;
      hideMP();
    });
    mpGrid.appendChild(b);
  });
}
// CLIC EN TODO EL INPUT, FOCUS Y ACCESIBILIDAD PARA ABRIR EL SELECTOR DE MESES
ymInput.addEventListener('click', () => showMP(ymInput));
ymInput.addEventListener('focus', () => showMP(ymInput));
ymInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' || e.key === 'ArrowDown' || e.key === ' ') {
    e.preventDefault();
    showMP(ymInput);
  }
});
// Icono también abre el selector
if (ymIc) ymIc.addEventListener('click', () => showMP(ymInput));

document.addEventListener('click', (e)=>{
  if (!mp.contains(e.target) && e.target!==ymInput && e.target!==ymIc) {
    if (mp.style.display==='block') hideMP();
  }
});
document.querySelector('.mph').addEventListener('click', (e)=>{
  const btn = e.target.closest('button'); if(!btn) return;
  const act = btn.getAttribute('data-mp');
  if (act==='prevY'){ mpYear--; renderMP(); }
  if (act==='nextY'){ mpYear++; renderMP(); }
  if (act==='thisY'){ mpYear = new Date().getFullYear(); renderMP(); }
});
</script>
