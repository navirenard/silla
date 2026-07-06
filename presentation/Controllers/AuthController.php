<?php

namespace App\Presentation\Controllers;

use Container;
use App\Application\UseCases\Auth\LoginUser;
use App\Application\UseCases\Auth\RegisterUser;
use App\Presentation\Middleware\AuthMiddleware;
use Exception;

class AuthController extends Controller
{
    /**
     * Tampilan halaman Login
     */
    public function showLogin(): void
    {
        if (AuthMiddleware::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $error   = isset($_GET['error'])   ? htmlspecialchars_decode(urldecode($_GET['error']))   : null;
        $success = isset($_GET['success']) ? htmlspecialchars_decode(urldecode($_GET['success'])) : null;

        $this->render('auth/login', [
            'title'   => 'Login - SiLLA',
            'error'   => $error,
            'success' => $success,
        ]);
    }

    /**
     * Proses Login
     */
    public function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        try {
            /** @var LoginUser $loginUseCase */
            $loginUseCase = Container::get(LoginUser::class);
            $result = $loginUseCase->execute($email, $password);

            $user = $result['user'];

            // Set stateless signed cookie — bekerja di Vercel serverless
            AuthMiddleware::setAuthCookie([
                'uid'   => $user->uid,
                'email' => $user->email,
                'name'  => $user->displayName,
                'role'  => $user->role,
            ]);

            $this->redirect('/dashboard');

        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'SQLSTATE') || str_contains($msg, 'Connection') || str_contains($msg, 'could not')) {
                $msg = 'Gagal terhubung ke server database. Periksa konfigurasi koneksi.';
            }
            $this->redirect('/login?error=' . urlencode($msg));
        }
    }

    /**
     * Tampilan halaman Register
     */
    public function showRegister(): void
    {
        if (AuthMiddleware::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $error = isset($_GET['error']) ? htmlspecialchars_decode(urldecode($_GET['error'])) : null;

        $this->render('auth/register', [
            'title' => 'Pendaftaran Akun - SiLLA',
            'error' => $error,
        ]);
    }

    /**
     * Proses Pendaftaran/Register
     */
    public function register(): void
    {
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        $role     = 'officer';

        try {
            /** @var RegisterUser $registerUseCase */
            $registerUseCase = Container::get(RegisterUser::class);
            $registerUseCase->execute($name, $email, $password, $role);

            $this->redirect('/login?success=' . urlencode('Pendaftaran berhasil! Silakan masuk menggunakan akun baru Anda.'));

        } catch (Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'SQLSTATE') || str_contains($msg, 'Connection') || str_contains($msg, 'could not')) {
                $msg = 'Gagal terhubung ke server database. Periksa konfigurasi koneksi.';
            }
            $this->redirect('/register?error=' . urlencode($msg));
        }
    }

    /**
     * Proses Logout
     */
    public function logout(): void
    {
        AuthMiddleware::clearAuthCookie();
        $this->redirect('/login?success=' . urlencode('Anda berhasil keluar dari sistem.'));
    }
}
