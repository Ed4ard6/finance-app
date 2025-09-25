<?php
// Front Controller: punto de entrada


require __DIR__ . '/../vendor/autoload.php';


// Cargar rutas y despachar
$router = require __DIR__ . '/../app/Routes/web.php';
$router->dispatch();
