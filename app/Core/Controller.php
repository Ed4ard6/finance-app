<?php

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $config  = require BASE_PATH . '/app/Config/config.php';
        $baseUrl = $config['base_url'];
        extract($data);

        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';
        $header   = BASE_PATH . '/app/Views/layouts/header.php';
        $footer   = BASE_PATH . '/app/Views/layouts/footer.php';
        
        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo 'Vista no encontrada: ' . $view . '<br>';
            echo 'Busqué en: ' . $viewPath; // línea de depuración útil
            return;
        }

        require $header;
        require $viewPath;
        require $footer;
    }
}
