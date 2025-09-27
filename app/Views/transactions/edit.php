<?php
$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = rtrim($config['base_url'] ?? '', '/');
?>
<style>
  .wrap{max-width:900px;margin:0 auto}
  .card{background:#0f172a;border:1px solid #1f2937;border-radius:12px;padding:16px;color:#e5e7eb}
  .row{display:flex;gap:12px;flex-wrap:wrap}
  .field{flex:1 1 220px;display:flex;flex-direction:column;gap:6px;margin-bottom:12px}
  .input,select{width:100%;background:#0b1220;border:1px solid #334155;color:#e5e7eb;border-radius:8px;padding:10px}
  .btn{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:8px;border:1px solid #334155;background:#0b1220;color:#e5e7eb;text-decoration:none;min-width:110px}
  .btn[disabled]{opacity:.6;cursor:not-allowed}
  .btn:hover{background:#111827}
  .btn-primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
  .form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:16px}
  .datebox{position:relative;cursor:pointer}
  .datebox input{padding-right:12px}
  input[type="date"]::-webkit-calendar-picker-indicator{opacity:0;display:none}
  input[type="date"]{appearance:none;-webkit-appearance:none}
  .datebox{position:relative}
  .datebox .ic{position:absolute;right:10px;top:50%;transform:translateY(-50%);opacity:.9;pointer-events:none}
</style>

<div class="wrap">
  <h1 style="font-size:26px;margin:12px 0">Editar transacción</h1>

  <form id="tx-edit-form" action="<?= $baseUrl ?>/transactions/update" method="post" class="card" autocomplete="off">
    <input type="hidden" name="id" value="<?= (int)$tx['id'] ?>">

    <div class="row">
      <div class="field" style="flex:1 1 220px">
        <label>Tipo</label>
        <select name="kind" id="kind" class="input" onchange="toggleCats()">
          <option value="expense" <?= ($type) === 'expense' ? 'selected' : '' ?>>Gasto</option>
          <option value="income"  <?= ($type) === 'income'  ? 'selected' : ''  ?>>Ingreso</option>
        </select>
      </div>

      <div class="field" style="flex:2 1 320px" id="catExpenseWrap">
        <label>Categoría (Egreso)</label>
        <select id="catExpense" name="category_id" class="input">
          <?php foreach ($catsExpense as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (int)$tx['category_id'] === $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field" style="flex:2 1 320px; display:none" id="catIncomeWrap">
        <label>Categoría (Ingreso)</label>
        <select id="catIncome" name="category_id" class="input" disabled>
          <?php foreach ($catsIncome as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (int)$tx['category_id'] === $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="field" style="flex:1 1 220px">
        <label>Fecha</label>
        <div class="datebox" id="date_at-wrap" aria-label="Abrir calendario">
          <input class="input dp-bind" id="date_at" type="date" name="date_at"
                 value="<?= htmlspecialchars($tx['date_at']) ?>">
          <span class="ic">📅</span>
        </div>
      </div>
      <div class="field" style="flex:1 1 220px">
        <label>Monto (COP)</label>
        <input
          class="input"
          id="amount"
          name="amount"
          type="text"
          inputmode="numeric"
          value="<?= htmlspecialchars(number_format(abs($tx['amount']), 0, ',', '.')) ?>"
          placeholder="p.ej. 120.000">
      </div>
    </div>

    <div class="row">
      <div class="field" style="flex:1 1 100%">
        <label>Descripción</label>
        <input class="input" type="text" name="description" value="<?= htmlspecialchars($tx['description'] ?? '') ?>">
      </div>
    </div>

    <div class="form-actions">
      <a class="btn" href="<?= $baseUrl ?>/transactions">Cancelar</a>
      <button id="btnSave" class="btn btn-primary" type="submit">Guardar cambios</button>
    </div>
  </form>
</div>

<!-- ========== Datepicker (plantilla) ========== -->
<style>
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
</style>
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

<script>
  function toggleCats(){
    const kind=document.getElementById('kind').value;
    const expWrap=document.getElementById('catExpenseWrap');
    const incWrap=document.getElementById('catIncomeWrap');
    const expSel=document.getElementById('catExpense');
    const incSel=document.getElementById('catIncome');
    if(kind==='expense'){
      expWrap.style.display='block'; expSel.disabled=false;
      incWrap.style.display='none';  incSel.disabled=true;
    }else{
      expWrap.style.display='none';  expSel.disabled=true;
      incWrap.style.display='block'; incSel.disabled=false;
    }
  }
  toggleCats();

  /* Botón Guardar solo si hay cambios */
  (function(){
    const form = document.getElementById('tx-edit-form');
    const save = document.getElementById('btnSave');
    if(!form||!save) return;

    const snap = {};
    new FormData(form).forEach((v,k)=>snap[k]=v);

    function changed(){
      const f=new FormData(form);
      for(const [k,v] of f.entries()){
        if((snap[k]??'')!==v) return true;
      }
      return false;
    }
    function toggle(){ save.disabled = !changed(); }

    form.addEventListener('input', toggle);
    form.addEventListener('change', toggle);

    // Observa cambios programáticos (por si algún script asigna .value)
    const mo = new MutationObserver(toggle);
    mo.observe(form, {subtree:true, attributes:true, attributeFilter:['value']});

    toggle();
  })();

  /* ================== Datepicker (salida YYYY-MM-DD) ================== */
  const dp = document.getElementById('datepicker');
  const dpTitle = document.getElementById('dp-title');
  const dpGrid = document.getElementById('dp-grid');
  let dpBindInput = null, dpMonth = new Date();

  function monthName(d){
    return d.toLocaleDateString('es-CO',{month:'long',year:'numeric'}).replace(/^\w/,c=>c.toUpperCase());
  }
  function formatYMD(d){
    const yy=d.getFullYear(), mm=String(d.getMonth()+1).padStart(2,'0'), dd=String(d.getDate()).padStart(2,'0');
    return `${yy}-${mm}-${dd}`;
  }
  function parseYMD(val){
    if(!/^\d{4}-\d{2}-\d{2}$/.test(val)) return null;
    const [y,m,d]=val.split('-').map(n=>parseInt(n,10));
    return new Date(y, m-1, d);
  }
  function renderDP(){
    dpTitle.textContent = monthName(dpMonth);
    dpGrid.innerHTML='';
    ['L','M','X','J','V','S','D'].forEach(w=>{
      const el=document.createElement('div'); el.className='dow'; el.textContent=w; dpGrid.appendChild(el);
    });
    const first=new Date(dpMonth.getFullYear(), dpMonth.getMonth(), 1);
    const startIdx=(first.getDay()+6)%7;
    const daysInMonth=new Date(dpMonth.getFullYear(), dpMonth.getMonth()+1, 0).getDate();
    const prevMonthDays=new Date(dpMonth.getFullYear(), dpMonth.getMonth(), 0).getDate();

    for(let i=0;i<startIdx;i++){
      const dd=prevMonthDays-startIdx+i+1;
      dpGrid.appendChild(mkDay(dd,true,new Date(dpMonth.getFullYear(), dpMonth.getMonth()-1, dd)));
    }
    for(let d=1; d<=daysInMonth; d++){
      dpGrid.appendChild(mkDay(d,false,new Date(dpMonth.getFullYear(), dpMonth.getMonth(), d)));
    }
    const totalCells=7+startIdx+daysInMonth;
    const extra=(totalCells%7===0)?0:(7-(totalCells%7));
    for(let d=1; d<=extra; d++){
      dpGrid.appendChild(mkDay(d,true,new Date(dpMonth.getFullYear(), dpMonth.getMonth()+1, d)));
    }
  }
  function mkDay(d, off, dateObj){
    const el=document.createElement('div');
    el.className='day'+(off?' off':'');
    const today=new Date(); const t0=new Date(today.getFullYear(), today.getMonth(), today.getDate());
    if(dateObj.toDateString()===t0.toDateString()) el.classList.add('today');
    el.textContent=d;
    el.addEventListener('click', ()=>{
      dpGrid.querySelectorAll('.day').forEach(x=>x.classList.remove('sel'));
      el.classList.add('sel');
      if(dpBindInput){
        dpBindInput.value = formatYMD(dateObj);
        // 🔔 Notifica al formulario que el valor cambió (habilita “Guardar”)
        dpBindInput.dispatchEvent(new Event('input',{bubbles:true}));
        dpBindInput.dispatchEvent(new Event('change',{bubbles:true}));
      }
    });
    return el;
  }
  function showDP(input){
    dpBindInput=input;
    const r=input.getBoundingClientRect();
    dp.style.left=(window.scrollX+r.left)+'px';
    dp.style.top =(window.scrollY+r.bottom+6)+'px';
    dp.style.display='block';
    const v=input.value.trim();
    const d=parseYMD(v);
    dpMonth = d ? new Date(d.getFullYear(), d.getMonth(), 1) : new Date();
    renderDP();
  }
  function hideDP(){ dp.style.display='none'; dpBindInput=null; }

  dp.addEventListener('click', e=>{
    const act=e.target.getAttribute('data-dp'); if(!act) return;
    if(act==='prev'){ dpMonth=new Date(dpMonth.getFullYear(), dpMonth.getMonth()-1, 1); renderDP(); }
    if(act==='next'){ dpMonth=new Date(dpMonth.getFullYear(), dpMonth.getMonth()+1, 1); renderDP(); }
    if(act==='today'){ dpMonth=new Date(); renderDP(); }
    if(act==='ok'){ hideDP(); }
  });
  document.querySelectorAll('.dp-bind').forEach(inp=>{
    const wrap = inp.closest('.datebox') || inp;
    wrap.addEventListener('click', ()=> showDP(inp));
    // Si el usuario escribe a mano, igual habilitamos Guardar
    inp.addEventListener('input', ()=> {
      inp.dispatchEvent(new Event('change',{bubbles:true}));
    });
  });
  document.addEventListener('click', (e)=>{
    if(!dp.contains(e.target) && !e.target.classList.contains('dp-bind') && !e.target.closest('.datebox')){
      if(dp.style.display==='block') hideDP();
    }
  });
</script>
