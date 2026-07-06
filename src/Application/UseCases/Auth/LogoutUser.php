<?php

namespace App\Application\UseCases\Auth;

class LogoutUser
{
    /**
     * Logout hanya perlu membersihkan session pada sisi PHP karena REST API stateless.
     */
    public function execute(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Bersihkan semua data session
        $_SESSION = [];

        // Hapus cookie session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        // Hancurkan session
        session_destroy();
    }
}
