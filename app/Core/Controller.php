<?php

namespace App\Core;

class Controller
{
    protected function view(string $viewFile, array $data = []): void
    {
        $config  = require BASE_PATH . '/app/Config/config.php';
        $baseUrl = $config['base_url'];

        // ¡IMPORTANTE! Evitar colisiones con claves de $data (ej: 'view' => 'all')
        // Usamos EXTR_SKIP y NO usamos el nombre $view para el archivo.
        extract($data, EXTR_SKIP);

        // Construir ruta usando la variable del parámetro ($viewFile), no $view.
        $viewPath = BASE_PATH . '/app/Views/' . ltrim($viewFile, '/') . '.php';
        $header   = BASE_PATH . '/app/Views/layouts/header.php';
        $footer   = BASE_PATH . '/app/Views/layouts/footer.php';

        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo 'Vista no encontrada: ' . htmlspecialchars($viewFile) . '<br>';
            echo 'Busqué en: ' . htmlspecialchars($viewPath);
            return;
        }

        require $header;
        require $viewPath;
        require $footer;
    }
}
