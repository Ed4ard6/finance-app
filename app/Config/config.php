<?php
return [
    'base_url' => $_ENV['BASE_URL'] ?? 'http://finance.test',
    'db_host'  => $_ENV['DB_HOST']  ?? 'localhost',
    'db_name'  => $_ENV['DB_NAME']  ?? 'finanzas',
    'db_user'  => $_ENV['DB_USER']  ?? 'root',
    'db_pass'  => $_ENV['DB_PASS']  ?? '',

];
