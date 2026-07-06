<?php

/**
 * Firebase Configuration
 * 
 * Ganti nilai-nilai di bawah dengan konfigurasi Firebase project Anda.
 * Dapatkan dari Firebase Console > Project Settings > General
 */

return [
    // Firebase Web API Key (dari Firebase Console > Project Settings)
    'api_key' => getenv('FIREBASE_API_KEY') ?: 'YOUR_FIREBASE_API_KEY',

    // Firebase Project ID
    'project_id' => getenv('FIREBASE_PROJECT_ID') ?: 'YOUR_PROJECT_ID',

    // Firebase Realtime Database URL
    'database_url' => getenv('FIREBASE_DATABASE_URL') ?: 'https://YOUR_PROJECT_ID.firebaseio.com',

    // Firebase Auth REST API base URL
    'auth_url' => 'https://identitytoolkit.googleapis.com/v1',

    // Firebase Auth Token Refresh URL
    'token_refresh_url' => 'https://securetoken.googleapis.com/v1/token',
];
