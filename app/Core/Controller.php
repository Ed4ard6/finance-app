<?php

namespace App\Core;


class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $config = require __DIR__ . '/../Config/config.php';
        extract($data); // hace disponibles variables en la vista


        $baseUrl = $config['base_url']; // útil en vistas para links/recursos


        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        $header = __DIR__ . '/../Views/layouts/header.php';
        $footer = __DIR__ . '/../Views/layouts/footer.php';


        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo "Vista no encontrada: $view";
            return;
        }


        require $header;
        require $viewPath;
        require $footer;
    }
}
