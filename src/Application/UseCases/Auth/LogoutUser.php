<?php

namespace App\Application\UseCases\Auth;

use App\Presentation\Middleware\AuthMiddleware;

class LogoutUser
{
    /**
     * Logout: hapus cookie auth stateless.
     */
    public function execute(): void
    {
        AuthMiddleware::clearAuthCookie();
    }
}
