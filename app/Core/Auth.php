<?php
namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        return (bool) self::currentUserId();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            $base = (require BASE_PATH.'/app/Config/config.php')['base_url'] ?? '';
            header('Location: '.$base.'/login'); exit;
        }
    }

    /**
     * Fuente única de verdad para el user_id.
     * - Primero intenta Session::get('user')['id']
     * - Luego $_SESSION['user_id']
     */
    public static function currentUserId(): ?int
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        // 1) Objeto de usuario que guardas en Session::get('user')
        $user = Session::get('user');
        if (is_array($user) && !empty($user['id'])) {
            return (int) $user['id'];
        }

        // 2) Fallback: variable suelta en $_SESSION
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        return null;
    }
}
