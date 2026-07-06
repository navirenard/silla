<?php

/**
 * Application Configuration
 */

return [
    'name' => 'SiLLA - Sistem Layanan Loket Antrian',
    'version' => '1.0.0',
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id_ID',
    
    // Queue settings
    'queue' => [
        'prefix' => 'A',          // Prefix nomor antrian
        'daily_reset' => true,     // Reset nomor antrian setiap hari
        'max_per_day' => 999,      // Maksimal antrian per hari
    ],

    // Session settings
    'session' => [
        'name' => 'silla_session',
        'lifetime' => 7200,        // 2 jam
    ],

    // Roles
    'roles' => [
        'admin' => 'Admin',
        'officer' => 'Petugas',
    ],
];
