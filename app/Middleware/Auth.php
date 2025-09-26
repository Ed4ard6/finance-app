<?php
namespace App\Middleware;

class Auth
{
    public function __invoke()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: /login?redirect=/dashboard');
            exit;
        }
    }
}
