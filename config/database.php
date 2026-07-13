<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // XAMPP default: empty
define('DB_NAME', 'cms_baladiya');

// Create connection with FULL error display
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("
    <div style='background:#0f0f1a;color:#ef4444;padding:40px;font-family:monospace;text-align:center;'>
        <h2>❌ Database Connection Failed</h2>
        <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <hr style='border-color:#334155;margin:20px 0;'>
        <p style='color:#94a3b8;'>Check these:</p>
        <ul style='color:#94a3b8;text-align:left;display:inline-block;'>
            <li>Is MySQL running in XAMPP Control Panel?</li>
            <li>Did you create database <code style='color:#6366f1;'>cms_baladiya</code>?</li>
            <li>Is your password correct? (XAMPP default is empty)</li>
            <li>Did you import <code style='color:#6366f1;'>database/schema.sql</code>?</li>
        </ul>
        <br><br>
        <a href='http://localhost/phpmyadmin/' target='_blank' style='color:#6366f1;'>Open phpMyAdmin →</a>
    </div>");
}

function getDB() {
    global $pdo;
    return $pdo;
}
?>