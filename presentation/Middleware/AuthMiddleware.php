<?php

namespace App\Presentation\Middleware;

/**
 * AuthMiddleware — Stateless Cookie-Based Auth
 * 
 * Menggunakan HMAC-signed cookie agar kompatibel dengan
 * Vercel serverless (tidak ada shared session storage).
 * 
 * Cookie: silla_auth
 * Format: base64(json_payload)|hmac_signature
 */
class AuthMiddleware
{
    private const COOKIE_NAME   = 'silla_auth';
    private const COOKIE_TTL    = 7200; // 2 jam
    private const SECRET_KEY    = 'silla_secret_key_2026_puskesmas_salem';

    // ----------------------------------------------------------------
    // Cookie helpers
    // ----------------------------------------------------------------

    /**
     * Buat signed cookie auth setelah login berhasil.
     */
    public static function setAuthCookie(array $payload): void
    {
        $json    = base64_encode(json_encode($payload));
        $sig     = hash_hmac('sha256', $json, self::SECRET_KEY);
        $value   = $json . '|' . $sig;

        setcookie(
            self::COOKIE_NAME,
            $value,
            [
                'expires'  => time() + self::COOKIE_TTL,
                'path'     => '/',
                'httponly' => true,
                'secure'   => isset($_SERVER['HTTPS']) || (getenv('VERCEL') === '1'),
                'samesite' => 'Lax',
            ]
        );

        // Juga sync ke $_COOKIE agar langsung terbaca di request yang sama
        $_COOKIE[self::COOKIE_NAME] = $value;
    }

    /**
     * Hapus cookie auth saat logout.
     */
    public static function clearAuthCookie(): void
    {
        setcookie(self::COOKIE_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[self::COOKIE_NAME]);
    }

    /**
     * Baca dan verifikasi cookie — kembalikan payload atau null jika invalid.
     */
    public static function getPayload(): ?array
    {
        $raw = $_COOKIE[self::COOKIE_NAME] ?? null;
        if (!$raw) return null;

        $parts = explode('|', $raw, 2);
        if (count($parts) !== 2) return null;

        [$json, $sig] = $parts;

        // Verifikasi HMAC signature
        $expected = hash_hmac('sha256', $json, self::SECRET_KEY);
        if (!hash_equals($expected, $sig)) return null;

        $data = json_decode(base64_decode($json), true);
        if (!is_array($data)) return null;

        return $data;
    }

    // ----------------------------------------------------------------
    // Auth checks
    // ----------------------------------------------------------------

    /**
     * Memeriksa apakah user sudah login (via cookie).
     */
    public static function isAuthenticated(): bool
    {
        $payload = self::getPayload();
        return $payload !== null && !empty($payload['uid']);
    }

    /**
     * Memeriksa apakah user adalah Admin.
     */
    public static function isAdmin(): bool
    {
        $payload = self::getPayload();
        return $payload !== null && ($payload['role'] ?? '') === 'admin';
    }

    /**
     * Dapatkan data user yang sedang login.
     */
    public static function getUser(): ?array
    {
        return self::getPayload();
    }

    /**
     * Proteksi halaman: wajib login.
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: ' . url('/login?error=' . urlencode('Silakan login terlebih dahulu untuk mengakses halaman ini.')));
            exit();
        }
    }

    /**
     * Proteksi halaman: wajib admin.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (!self::isAdmin()) {
            header('Location: ' . url('/dashboard?error=' . urlencode('Anda tidak memiliki hak akses Admin untuk halaman tersebut.')));
            exit();
        }
    }
}
