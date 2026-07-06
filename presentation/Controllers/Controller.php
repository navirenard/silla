<?php

namespace App\Presentation\Controllers;

abstract class Controller
{
    /**
     * Render view dengan layout utama.
     */
    protected function render(string $viewPath, array $data = []): void
    {
        // Extract data agar bisa langsung diakses di view template sebagai variabel ($name, $queues, dll)
        extract($data);

        // Path ke file view spesifik
        $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            die("View '{$viewPath}' tidak ditemukan di {$viewFile}");
        }

        // Start output buffering untuk menangkap HTML dari view
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Include layout utama yang akan merender variable $content
        include __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Mengembalikan response JSON untuk API / AJAX call.
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    /**
     * Helper untuk redirect
     */
    protected function redirect(string $url): void
    {
        header("Location: " . url($url));
        exit();
    }
}
