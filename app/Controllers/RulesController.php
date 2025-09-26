<?php
namespace App\Controllers;

use App\Core\Controller;

class RulesController extends Controller
{
    public function generate()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['user_id'])) { header('Location: /login?redirect=/dashboard'); exit; }

        $_SESSION['flash_success'] = 'Pronto: generación desde reglas. (Aún no hay reglas configuradas)';
        header('Location: /dashboard'); exit;
    }
}
