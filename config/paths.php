<?php
// Auto-detect base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Get the folder path (e.g., /cms-baladiya-/)
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname(dirname($scriptName)); // Go up 2 levels from any file
$basePath = str_replace('\\', '/', $basePath);
if ($basePath == '/') $basePath = '';

define('BASE_URL', $protocol . '://' . $host . $basePath);
define('ASSETS_URL', BASE_URL . '/assets');
?>