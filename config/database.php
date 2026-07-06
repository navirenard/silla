<?php

/**
 * Database Configuration
 * 
 * Supports both MySQL (local/Laragon) and PostgreSQL (Supabase).
 * Set DB_DRIVER to 'pgsql' for Supabase, 'mysql' for local MySQL.
 */

$driver = getenv('DB_DRIVER') ?: 'pgsql'; // Ubah ke 'mysql' jika pakai lokal

return [
    'driver'   => $driver,

    // Gunakan Session Pooler (IPv4 compatible), bukan Direct Connection (IPv6 only).
    // Host & username di bawah didapat dari:
    //   Supabase → Project Settings → Database → Connection Method: Session pooler
    'host'     => getenv('DB_HOST')     ?: 'aws-1-ap-southeast-1.pooler.supabase.com',
    'port'     => getenv('DB_PORT')     ?: ($driver === 'pgsql' ? '5432' : '3306'),
    'dbname'   => getenv('DB_DATABASE') ?: 'postgres',
    'username' => getenv('DB_USERNAME') ?: 'postgres.qlkpvfhqpnvljqcskegs', // Format pooler: postgres.[project-id]
    'password' => getenv('DB_PASSWORD') ?: 'faiz,cirebon',
    'charset'  => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
