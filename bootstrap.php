<?php

/**
 * Helper global untuk merender URL dinamis sesuai base-path (subfolder friendly)
 */
function url(string $path): string
{
    // Jika berjalan di Vercel, app selalu di root level
    if (isset($_SERVER['VERCEL']) || getenv('VERCEL') === '1') {
        return '/' . ltrim($path, '/');
    }

    // Gunakan SCRIPT_FILENAME (path fisik script) vs DOCUMENT_ROOT
    // agar kompatibel dengan Laragon di Windows tanpa perlu vhost setup.
    $docRoot    = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $scriptFile = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $scriptDir  = rtrim(dirname($scriptFile), '/');
    $basePath   = ($docRoot && strpos($scriptDir, $docRoot) === 0)
        ? substr($scriptDir, strlen($docRoot))
        : '';

    return $basePath . '/' . ltrim($path, '/');
}

/**
 * SiLLA Bootstrap File
 * 
 * Mengatur autoloader PSR-4, inisialisasi session, dan menyiapkan Dependency Injection container.
 */

// 1. PSR-4 Autoloader Manual
spl_autoload_register(function ($class) {
    // Prefix namespace proyek
    $prefix = 'App\\';
    
    // Base directory untuk prefix namespace
    $baseDir = __DIR__ . '/';
    
    // Apakah class menggunakan prefix namespace ini?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Tidak menggunakan, biarkan autoloader lain (jika ada) yang menghandle
        return;
    }
    
    // Dapatkan relative class name
    $relativeClass = substr($class, $len);
    
    // Ubah namespace backslash (\) menjadi directory slash (/)
    // Untuk windows, replace dengan DIRECTORY_SEPARATOR
    // Dan sesuaikan struktur direktori:
    // Jika namespace dimulai dengan 'Presentation', arahkan ke direktori 'presentation/'
    // Jika tidak, arahkan ke direktori 'src/'
    if (strpos($relativeClass, 'Presentation\\') === 0) {
        // App\Presentation\Controllers\AuthController -> presentation/Controllers/AuthController.php
        $relativeClass = substr($relativeClass, strlen('Presentation\\'));
        $file = $baseDir . 'presentation/' . str_replace('\\', '/', $relativeClass) . '.php';
    } else {
        // App\Domain\Entities\User -> src/Domain/Entities/User.php
        $file = $baseDir . 'src/' . str_replace('\\', '/', $relativeClass) . '.php';
    }
    
    // Jika file ada, load file tersebut
    if (file_exists($file)) {
        require $file;
    }
});

// 2. Inisialisasi Session
$appConfig = require __DIR__ . '/config/app.php';
date_default_timezone_set($appConfig['timezone'] ?? 'Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_name($appConfig['session']['name'] ?? 'silla_session');
    session_set_cookie_params($appConfig['session']['lifetime'] ?? 7200);
    session_start();
}

// 3. Simple Dependency Injection Container / Service Locator
class Container
{
    private static array $instances = [];

    public static function get(string $key, array $config = [])
    {
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        $appConfig = require __DIR__ . '/config/app.php';

        switch ($key) {
            case PDO::class:
                $dbConfig = require __DIR__ . '/config/database.php';
                $driver   = $dbConfig['driver'] ?? 'mysql';

                // Build DSN berdasarkan driver (mysql = lokal/Laragon, pgsql = Supabase)
                if ($driver === 'pgsql') {
                    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
                } else {
                    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
                }

                $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

                // Auto-seed database jika kosong
                try {
                    $nowFn = ($driver === 'pgsql') ? 'CURRENT_TIMESTAMP' : 'NOW()';

                    // 1. Cek & Seed Counters jika kosong
                    $stmt = $pdo->query("SELECT COUNT(*) FROM counters");
                    if ($stmt && $stmt->fetchColumn() == 0) {
                        $isActiveVal = ($driver === 'pgsql') ? 'TRUE' : '1';
                        $pdo->exec("
                            INSERT INTO counters (id, name, is_active, current_queue_number, current_queue_id, assigned_officer_uid) 
                            VALUES 
                            ('c_loket1', 'Loket 1', {$isActiveVal}, NULL, NULL, NULL),
                            ('c_loket2', 'Loket 2', {$isActiveVal}, NULL, NULL, NULL),
                            ('c_loket3', 'Loket 3', {$isActiveVal}, NULL, NULL, NULL)
                        ");
                    }

                    // 2. Cek & Seed Admin jika kosong
                    $stmtAdmin = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
                    if ($stmtAdmin && $stmtAdmin->fetchColumn() == 0) {
                        $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
                        $stmtInsert = $pdo->prepare("
                            INSERT INTO users (uid, email, password, display_name, role, created_at) 
                            VALUES ('u_admin', 'admin@silla.com', :password, 'Administrator', 'admin', {$nowFn})
                        ");
                        $stmtInsert->execute(['password' => $hashedPassword]);
                    }
                } catch (\Throwable $e) {
                    // Hiraukan error jika table belum terbentuk
                }

                self::$instances[$key] = $pdo;
                break;

            case \App\Domain\Repositories\UserRepositoryInterface::class:
                $pdo = self::get(PDO::class);
                self::$instances[$key] = new \App\Infrastructure\Repositories\MySQLUserRepository($pdo);
                break;

            case \App\Domain\Repositories\QueueRepositoryInterface::class:
                $pdo = self::get(PDO::class);
                self::$instances[$key] = new \App\Infrastructure\Repositories\MySQLQueueRepository($pdo);
                break;

            case \App\Domain\Repositories\CounterRepositoryInterface::class:
                $pdo = self::get(PDO::class);
                self::$instances[$key] = new \App\Infrastructure\Repositories\MySQLCounterRepository($pdo);
                break;

            // Auth Use Cases
            case \App\Application\UseCases\Auth\RegisterUser::class:
                $userRepo = self::get(\App\Domain\Repositories\UserRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Auth\RegisterUser($userRepo);
                break;

            case \App\Application\UseCases\Auth\LoginUser::class:
                $userRepo = self::get(\App\Domain\Repositories\UserRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Auth\LoginUser($userRepo);
                break;

            case \App\Application\UseCases\Auth\LogoutUser::class:
                self::$instances[$key] = new \App\Application\UseCases\Auth\LogoutUser();
                break;

            // Queue Use Cases
            case \App\Application\UseCases\Queue\CreateQueue::class:
                $queueRepo = self::get(\App\Domain\Repositories\QueueRepositoryInterface::class);
                $prefix = $appConfig['queue']['prefix'] ?? 'A';
                self::$instances[$key] = new \App\Application\UseCases\Queue\CreateQueue($queueRepo, $prefix);
                break;

            case \App\Application\UseCases\Queue\CallNextQueue::class:
                $queueRepo = self::get(\App\Domain\Repositories\QueueRepositoryInterface::class);
                $counterRepo = self::get(\App\Domain\Repositories\CounterRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Queue\CallNextQueue($queueRepo, $counterRepo);
                break;

            case \App\Application\UseCases\Queue\CompleteQueue::class:
                $queueRepo = self::get(\App\Domain\Repositories\QueueRepositoryInterface::class);
                $counterRepo = self::get(\App\Domain\Repositories\CounterRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Queue\CompleteQueue($queueRepo, $counterRepo);
                break;

            case \App\Application\UseCases\Queue\SkipQueue::class:
                $queueRepo = self::get(\App\Domain\Repositories\QueueRepositoryInterface::class);
                $counterRepo = self::get(\App\Domain\Repositories\CounterRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Queue\SkipQueue($queueRepo, $counterRepo);
                break;

            case \App\Application\UseCases\Queue\GetQueueList::class:
                $queueRepo = self::get(\App\Domain\Repositories\QueueRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Queue\GetQueueList($queueRepo);
                break;

            // Counter Use Cases
            case \App\Application\UseCases\Counter\CreateCounter::class:
                $counterRepo = self::get(\App\Domain\Repositories\CounterRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Counter\CreateCounter($counterRepo);
                break;

            case \App\Application\UseCases\Counter\UpdateCounter::class:
                $counterRepo = self::get(\App\Domain\Repositories\CounterRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Counter\UpdateCounter($counterRepo);
                break;

            case \App\Application\UseCases\Counter\DeleteCounter::class:
                $counterRepo = self::get(\App\Domain\Repositories\CounterRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Counter\DeleteCounter($counterRepo);
                break;

            case \App\Application\UseCases\Counter\GetCounters::class:
                $counterRepo = self::get(\App\Domain\Repositories\CounterRepositoryInterface::class);
                self::$instances[$key] = new \App\Application\UseCases\Counter\GetCounters($counterRepo);
                break;

            default:
                throw new Exception("Class {$key} tidak terdaftar di DI Container.");
        }

        return self::$instances[$key];
    }
}
