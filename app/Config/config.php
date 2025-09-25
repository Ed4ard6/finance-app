<?php
return [
    // Cambia estos valores en producción (Hostinger)
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_name' => getenv('DB_NAME') ?: 'finanzas',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',


    // Úsalo para generar links correctamente (local vs hosting)
    //'base_url' => getenv('BASE_URL') ?: 'http://localhost/finanzas-app/public',
    'base_url' => getenv('BASE_URL') ?: 'http://finance.test',

    
];
