<?php

namespace App\Presentation\Middleware;

class AuthMiddleware
{
    /**
     * Memeriksa apakah user sudah login.
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_uid']) && isset($_SESSION['firebase_token']);
    }

    /**
     * Memeriksa apakah user login adalah Admin.
     */
    public static function isAdmin(): bool
    {
        return self::isAuthenticated() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Proteksi halaman: Wajib login. Jika belum, lempar ke login.
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            $_SESSION['auth_error'] = "Silakan login terlebih dahulu untuk mengakses halaman ini.";
            header('Location: ' . url('/login'));
            exit();
        }
    }

    /**
     * Proteksi halaman: Wajib admin. Jika bukan, lempar ke dashboard.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        
        if (!self::isAdmin()) {
            $_SESSION['error'] = "Anda tidak memiliki hak akses (Admin) untuk halaman tersebut.";
            header('Location: ' . url('/dashboard'));
            exit();
        }
    }
}
