<?php
// includes/config.php

define('APP_NAME', 'TCC Inventory');
define('APP_VERSION', '1.0.0');

// Database configuration
// Checks if running in Docker (where DB_HOST is usually set), otherwise fall back to XAMPP defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'school_inventory');

// Base URL setup (for routing/assets)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
// Normalize base_url
$base_url = $protocol . '://' . $host . $base_dir;

$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);

$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$dir = str_replace('\\', '/', __DIR__); // .../includes
$root_dir = dirname($dir); // .../

// Perform case-insensitive match for the drive letters and paths
$uri_path = '';
if (!empty($doc_root)) {
    // If root_dir starts with doc_root (case-insensitive)
    if (stripos($root_dir, $doc_root) === 0) {
        $uri_path = substr($root_dir, strlen($doc_root));
    } else {
        // Fallback for cases where drive letters have different casing (e.g. C: vs c:)
        $root_dir_lower = strtolower($root_dir);
        $doc_root_lower = strtolower($doc_root);
        if (stripos($root_dir_lower, $doc_root_lower) === 0) {
            $uri_path = substr($root_dir, strlen($doc_root));
        }
    }
}

// Fallback if uri_path couldn't be extracted
if (empty($uri_path) || $uri_path === $root_dir) {
    // Attempt script folder extraction
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (strpos($script_dir, '/pages') !== false) {
        $uri_path = str_replace('/pages', '', $script_dir);
    } else {
        $uri_path = $script_dir;
    }
}

// Format the path nicely
$uri_path = '/' . ltrim(str_replace('\\', '/', $uri_path), '/');
$uri_path = rtrim($uri_path, '/') . '/';

define('BASE_URL', $uri_path);

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting (Dev mode)
error_reporting(E_ALL);
ini_set('display_errors', 1);
