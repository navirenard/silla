<?php

/**
 * SiLLA - Front Controller & Router
 */

require_once __DIR__ . '/../bootstrap.php';

// Parse URL Path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl = parse_url($requestUri);
$rawPath = $parsedUrl['path'] ?? '/';

// Determine base path by comparing DOCUMENT_ROOT with the physical
// location of this index.php file. This works correctly on Windows
// and with Laragon virtual host setups.
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$scriptFile = str_replace('\\', '/', realpath(__FILE__));
$scriptDir  = rtrim(dirname($scriptFile), '/');
$basePath   = ($docRoot && strpos($scriptDir, $docRoot) === 0)
    ? substr($scriptDir, strlen($docRoot))
    : '';

$path = ($basePath !== '')
    ? preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $rawPath)
    : $rawPath;
$path = $path ?: '/';

// Method Request (GET/POST)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Router Map
$routes = [
    'GET' => [
        '/' => [\App\Presentation\Controllers\DashboardController::class, 'index'],
        '/login' => [\App\Presentation\Controllers\AuthController::class, 'showLogin'],
        '/register' => [\App\Presentation\Controllers\AuthController::class, 'showRegister'],
        '/logout' => [\App\Presentation\Controllers\AuthController::class, 'logout'],
        '/dashboard' => [\App\Presentation\Controllers\DashboardController::class, 'index'],
        '/queues' => [\App\Presentation\Controllers\QueueController::class, 'index'],
        '/queues/history' => [\App\Presentation\Controllers\QueueController::class, 'history'],
        '/display' => [\App\Presentation\Controllers\QueueController::class, 'display'],
        '/display/data' => [\App\Presentation\Controllers\QueueController::class, 'displayData'],
        '/kiosk' => [\App\Presentation\Controllers\QueueController::class, 'kiosk'],
        '/counters' => [\App\Presentation\Controllers\CounterController::class, 'index'],
    ],
    'POST' => [
        '/login' => [\App\Presentation\Controllers\AuthController::class, 'login'],
        '/register' => [\App\Presentation\Controllers\AuthController::class, 'register'],
        '/queues/select-counter' => [\App\Presentation\Controllers\QueueController::class, 'selectCounter'],
        '/queues/create' => [\App\Presentation\Controllers\QueueController::class, 'create'],
        '/queues/call' => [\App\Presentation\Controllers\QueueController::class, 'callNext'],
        '/queues/complete' => [\App\Presentation\Controllers\QueueController::class, 'complete'],
        '/queues/skip' => [\App\Presentation\Controllers\QueueController::class, 'skip'],
        '/counters/create' => [\App\Presentation\Controllers\CounterController::class, 'create'],
        '/counters/update' => [\App\Presentation\Controllers\CounterController::class, 'update'],
        '/counters/delete' => [\App\Presentation\Controllers\CounterController::class, 'delete'],
    ]
];

// Cek kecocokan route
if (isset($routes[$method][$path])) {
    $controllerClass = $routes[$method][$path][0];
    $action = $routes[$method][$path][1];
    
    try {
        $controller = new $controllerClass();
        $controller->$action();
    } catch (\Throwable $e) {
        // Tampilkan error page yang cantik
        header('Content-Type: text/html; charset=utf-8');
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error 500 - SiLLA</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
            <style>
                body {
                    background-color: #0f172a;
                    color: #f8fafc;
                    font-family: 'Inter', sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .container {
                    background: rgba(30, 41, 59, 0.7);
                    backdrop-filter: blur(16px);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 16px;
                    padding: 40px;
                    max-width: 600px;
                    width: 90%;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
                }
                h1 { color: #f43f5e; margin-top: 0; font-size: 2rem; }
                p { color: #94a3b8; line-height: 1.6; }
                pre {
                    background: #020617;
                    padding: 15px;
                    border-radius: 8px;
                    overflow-x: auto;
                    color: #38bdf8;
                    font-size: 0.85rem;
                    border: 1px solid rgba(255, 255, 255, 0.05);
                }
                .btn {
                    display: inline-block;
                    background: #3b82f6;
                    color: #fff;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    margin-top: 20px;
                    transition: background 0.2s;
                }
                .btn:hover { background: #2563eb; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Terjadi Kesalahan Sistem</h1>
                <p>Aplikasi mengalami kendala teknis saat memproses permintaan Anda. Detail error:</p>
                <pre><?= htmlspecialchars($e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine()) ?></pre>
                <a href="<?= url('/dashboard') ?>" class="btn">Kembali ke Dashboard</a>
            </div>
        </body>
        </html>
        <?php
    }
} else {
    // 404 Not Found
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Halaman Tidak Ditemukan - SiLLA</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                background-color: #0f172a;
                color: #f8fafc;
                font-family: 'Inter', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .container {
                text-align: center;
                background: rgba(30, 41, 59, 0.7);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 16px;
                padding: 50px 40px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            }
            h1 { color: #38bdf8; font-size: 5rem; margin: 0; font-weight: 700; }
            h2 { font-size: 1.5rem; margin: 10px 0 20px 0; }
            p { color: #94a3b8; line-height: 1.6; margin-bottom: 30px; }
            .btn {
                display: inline-block;
                background: #3b82f6;
                color: #fff;
                padding: 12px 24px;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                transition: background 0.2s;
            }
            .btn:hover { background: #2563eb; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>404</h1>
            <h2>Halaman Tidak Ditemukan</h2>
            <p>Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan ke alamat lain.</p>
            <a href="<?= url('/dashboard') ?>" class="btn">Kembali ke Beranda</a>
        </div>
    </body>
    </html>
    <?php
}
