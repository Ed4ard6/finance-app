<?php if (App\Core\Session::get('user')): ?>
  <a href="<?= $baseUrl ?>/">Dashboard</a>
  <a href="<?= $baseUrl ?>/logout">Salir</a>
<?php else: ?>
  <a href="<?= $baseUrl ?>/login">Login</a>
  <a href="<?= $baseUrl ?>/register">Registro</a>
<?php endif; ?>


<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Finanzas</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/styles.css">
</head>

<body>
    <nav>
        <a href="<?= $baseUrl ?>/">Dashboard</a>
        <!-- Más links: Ingresos, Gastos, Metas, Reportes -->
    </nav>
    <main class="container">