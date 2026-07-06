<?php

namespace App\Presentation\Controllers;

use Container;
use App\Application\UseCases\Auth\LoginUser;
use App\Application\UseCases\Auth\RegisterUser;
use App\Application\UseCases\Auth\LogoutUser;
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

        $error = $_SESSION['auth_error'] ?? null;
        $success = $_SESSION['auth_success'] ?? null;
        
        // Clear message session setelah dibaca
        unset($_SESSION['auth_error'], $_SESSION['auth_success']);

        $this->render('auth/login', [
            'title' => 'Login - SiLLA',
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Proses Login
     */
    public function login(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            /** @var LoginUser $loginUseCase */
            $loginUseCase = Container::get(LoginUser::class);
            $result = $loginUseCase->execute($email, $password);

            // Simpan detail user & token ke session
            $_SESSION['user_uid'] = $result['user']->uid;
            $_SESSION['user_email'] = $result['user']->email;
            $_SESSION['user_name'] = $result['user']->displayName;
            $_SESSION['user_role'] = $result['user']->role;
            $_SESSION['firebase_token'] = $result['idToken'];
            $_SESSION['refresh_token'] = $result['refreshToken'];

            $_SESSION['auth_success'] = "Selamat datang kembali, " . $result['user']->displayName . "!";
            $this->redirect('/dashboard');

        } catch (Exception $e) {
            $_SESSION['auth_error'] = $e->getMessage();
            $this->redirect('/login');
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

        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        $this->render('auth/register', [
            'title' => 'Pendaftaran Akun - SiLLA',
            'error' => $error
        ]);
    }

    /**
     * Proses Pendaftaran/Register
     */
    public function register(): void
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Pendaftaran via web default role-nya adalah 'officer' (petugas)
        // Admin didaftarkan secara terpisah atau diubah rolenya di DB
        $role = 'officer'; 

        try {
            /** @var RegisterUser $registerUseCase */
            $registerUseCase = Container::get(RegisterUser::class);
            $registerUseCase->execute($name, $email, $password, $role);

            $_SESSION['auth_success'] = "Pendaftaran berhasil! Silakan masuk menggunakan akun baru Anda.";
            $this->redirect('/login');

        } catch (Exception $e) {
            $_SESSION['auth_error'] = $e->getMessage();
            $this->redirect('/register');
        }
    }

    /**
     * Proses Logout
     */
    public function logout(): void
    {
        /** @var LogoutUser $logoutUseCase */
        $logoutUseCase = Container::get(LogoutUser::class);
        $logoutUseCase->execute();

        // Start session baru untuk menyimpan pesan sukses logout
        session_start();
        $_SESSION['auth_success'] = "Anda berhasil keluar dari sistem.";
        $this->redirect('/login');
    }
}
