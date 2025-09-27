<?php
use App\Core\Session;

$config  = require BASE_PATH . '/app/Config/config.php';
$baseUrl = $config['base_url'];

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$user = Session::get('user');
if (!$user && !empty($_SESSION['user_id'])) {
  $user = [
    'id'     => (int) $_SESSION['user_id'],
    'nombre' => $_SESSION['user_name'] ?? 'Usuario',
    'email'  => $_SESSION['user_email'] ?? null,
  ];
}

// Ruta actual para marcar activo
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function is_active($path, $current)
{
  return rtrim($path, '/') === rtrim($current, '/') ? 'active' : '';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $titulo ?? 'Finanzas' ?></title>
  <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/styles.css">
  <link rel="icon" type="image/png" href="<?= $baseUrl ?>/assets/img/favicon.png">
  <style>
    /* ====== Header y nav (base) ====== */
    .main-header { position: sticky; top: 0; z-index: 1000; background: #0b1320; border-bottom: 1px solid #1f2937; }
    .main-header .wrap { max-width: 1200px; margin: 0 auto; padding: .75rem 1rem; display: flex; align-items: center; gap: .75rem; }
    .main-header .logo h2 { margin: 0; color: #a78bfa; text-decoration: none; }
    .main-header .spacer { flex: 1; }

    .nav{ position: relative; display:flex; align-items:center; gap:.25rem; overflow-x:auto; overflow-y:visible; white-space:nowrap; scrollbar-width:none; -ms-overflow-style:none; }
    .nav::-webkit-scrollbar{ display:none; }

    .nav a, .summary-like{ display:inline-flex; align-items:center; gap:.35rem; padding:.45rem .65rem; border-radius:.5rem; text-decoration:none; color:#cbd5e1; border:1px solid transparent; background:transparent; cursor:pointer; }
    .nav a:hover, .summary-like:hover{ background:#111827; color:#fff; }
    .nav a.active{ background:#1f2937; color:#fff; border-color:#334155; }
    .nav a, .summary-like{ word-break:keep-all; }

    .dropdown-fixed{ position:fixed; min-width:240px; background:#0b1220; border:1px solid #1f2937; border-radius:.5rem; padding:.35rem; box-shadow:0 10px 20px rgba(0,0,0,.35); display:none; z-index:2000; }
    .dropdown-fixed.show{ display:block !important; }
    .dropdown-fixed a{ display:flex; padding:.5rem .6rem; border-radius:.4rem; color:#cbd5e1; text-decoration:none; }
    .dropdown-fixed a:hover{ background:#111827; color:#fff; }

    .btn-icon{ display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:.5rem; color:#e5e7eb; border:1px solid #334155; background:#0b1220; text-decoration:none; }
    .btn-icon:hover{ background:#111827; }

    .btn-burger{ display:none; }

    @media (max-width: 900px){
      .btn-burger{ display:inline-flex; }
      .main-header .wrap{ gap:.5rem; }
      .nav{
        position: fixed;
        left: 0; right: 0;
        top: calc(56px + env(safe-area-inset-top));
        background:#0b1220;
        border-bottom: 1px solid #1f2937;
        padding: .5rem .75rem;
        display: none;
        flex-direction: column;
        align-items: stretch;
        gap:.35rem;
        white-space: normal;
      }
      .nav.open{ display:flex; }
      .nav a, .summary-like{
        width: 100%;
        padding: .7rem .8rem;
        border: 1px solid transparent;
      }
      .main-header .spacer{ display:none; }
      .dropdown-fixed{
        position: static;
        width: 100%;
        min-width: 0;
        margin-top: .25rem;
        display: none;
        box-shadow: none;
      }
      .dropdown-fixed.show{ display:block !important; }
      .dropdown-fixed a{ padding:.55rem .65rem; }
    }
  </style>
</head>

<body class="<?= $pageClass ?? '' ?>">
  <header class="main-header">
    <div class="wrap">
      <button id="btn-nav" class="btn-icon btn-burger" aria-controls="main-nav" aria-expanded="false" aria-label="Abrir menú">☰</button>

      <div class="logo">
        <a href="<?= $baseUrl ?>/dashboard" style="text-decoration:none;">
          <h2>Finanzas</h2>
        </a>
      </div>

      <nav id="main-nav" class="nav" aria-label="Principal">
        <?php if (!$user): ?>
          <a class="<?= is_active($baseUrl . '/login', $baseUrl . $currentPath) ?>" href="<?= $baseUrl ?>/login">Iniciar sesión</a>
          <a class="<?= is_active($baseUrl . '/register', $baseUrl . $currentPath) ?>" href="<?= $baseUrl ?>/register">Registrarse</a>
          <a class="<?= is_active($baseUrl . '/demo', $baseUrl . $currentPath) ?>" href="<?= $baseUrl ?>/demo">Demo</a>
        <?php else: ?>
          <a class="<?= is_active('/dashboard', $currentPath) ?>" href="<?= $baseUrl ?>/dashboard">Panel general</a>
          <a class="<?= is_active('/transactions', $currentPath) ?>" href="<?= $baseUrl ?>/transactions">Transacciones</a>
          <a class="<?= is_active('/categories', $currentPath) ?>" href="<?= $baseUrl ?>/categories">Categorías</a> <!-- 👈 NUEVO -->
          <a class="<?= is_active('/budgets', $currentPath) ?>" href="<?= $baseUrl ?>/budgets">Presupuestos</a>
          <a class="<?= is_active('/rules', $currentPath) ?>" href="<?= $baseUrl ?>/rules">Reglas</a>
          <a class="<?= is_active('/savings', $currentPath) ?>" href="<?= $baseUrl ?>/savings">Ahorros</a>
          <a class="<?= is_active('/debts', $currentPath) ?>" href="<?= $baseUrl ?>/debts">Deudas</a>

          <button id="btn-reports" class="summary-like" aria-haspopup="true" aria-expanded="false">Reportes ▾</button>
        <?php endif; ?>
      </nav>

      <div class="spacer"></div>

      <?php if ($user): ?>
        <a class="btn-icon" href="<?= $baseUrl ?>/logout" title="Cerrar sesión">⎋</a>
      <?php endif; ?>
    </div>
  </header>

  <div id="menu-reports" class="dropdown-fixed" role="menu" aria-labelledby="btn-reports">
    <a class="<?= is_active('/debts/compare', $currentPath) ?>" href="<?= $baseUrl ?>/debts/compare" role="menuitem">Comparador de deudas (Bola de nieve / Avalancha)</a>
    <a class="<?= is_active('/reports/waterfall', $currentPath) ?>" href="<?= $baseUrl ?>/reports/waterfall" role="menuitem">Cascada del mes</a>
    <a class="<?= is_active('/reports/calendar', $currentPath) ?>" href="<?= $baseUrl ?>/reports/calendar" role="menuitem">Calendario (mapa de calor)</a>
    <a class="<?= is_active('/planner', $currentPath) ?>" href="<?= $baseUrl ?>/planner" role="menuitem">Planificador de quincena</a>
    <a class="<?= is_active('/reports/monthly', $currentPath) ?>" href="<?= $baseUrl ?>/reports/monthly" role="menuitem">Reporte del mes (narrado)</a>
  </div>

  <main class="container">
    <script>
      (function() {
        const navBtn = document.getElementById('btn-nav');
        const nav    = document.getElementById('main-nav');
        if (!navBtn || !nav) return;

        function isMobile(){ return window.matchMedia('(max-width: 900px)').matches; }
        function toggleNav(force){
          const willOpen = force !== undefined ? force : !nav.classList.contains('open');
          nav.classList.toggle('open', willOpen);
          navBtn.setAttribute('aria-expanded', String(willOpen));
        }
        navBtn.addEventListener('click', (e)=>{ e.preventDefault(); toggleNav(); });
        window.addEventListener('resize', () => {
          if (!isMobile()) { nav.classList.remove('open'); navBtn.setAttribute('aria-expanded','false'); }
        });
        document.addEventListener('click', (e)=>{
          if (!isMobile()) return;
          if (!nav.contains(e.target) && !navBtn.contains(e.target)) {
            nav.classList.remove('open'); navBtn.setAttribute('aria-expanded','false');
          }
        });
      })();

      (function() {
        const btn  = document.getElementById('btn-reports');
        const menu = document.getElementById('menu-reports');
        const nav  = document.getElementById('main-nav');
        if (!btn || !menu) return;

        function isMobile(){ return window.matchMedia('(max-width: 900px)').matches; }
        function placeDesktop() {
          const r = btn.getBoundingClientRect();
          const menuW = Math.max(menu.offsetWidth, 240);
          let left = r.left;
          const rightSpace = window.innerWidth - (left + menuW);
          if (rightSpace < 8) left = Math.max(8, window.innerWidth - menuW - 8);
          const top = r.bottom + 8;
          menu.style.left = left + 'px';
          menu.style.top  = top  + 'px';
        }
        function openMenu(){ if (!isMobile()) { placeDesktop(); } menu.classList.add('show'); btn.setAttribute('aria-expanded','true'); }
        function closeMenu(){ menu.classList.remove('show'); btn.setAttribute('aria-expanded','false'); }

        btn.addEventListener('click', (e)=>{
          e.preventDefault();
          const open = menu.classList.contains('show');
          if (open) { closeMenu(); return; }
          if (isMobile() && nav && !nav.classList.contains('open')) {
            nav.classList.add('open');
            const navBtn = document.getElementById('btn-nav');
            if (navBtn) navBtn.setAttribute('aria-expanded','true');
          }
          openMenu();
        });
        document.addEventListener('click', (e)=>{ if (btn.contains(e.target) || menu.contains(e.target)) return; closeMenu(); });
        document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') closeMenu(); });
        window.addEventListener('resize', closeMenu);
        window.addEventListener('scroll', closeMenu, true);
      })();
    </script>
