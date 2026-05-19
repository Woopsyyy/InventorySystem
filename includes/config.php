<?php
define('APP_NAME', 'TCC Inventory');
define('APP_VERSION', '1.0.0');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'school_inventory');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
$base_url = $protocol . '://' . $host . $base_dir;

$script_name = $_SERVER['SCRIPT_NAME'];
$script_dir = dirname($script_name);

$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
$dir = str_replace('\\', '/', __DIR__);
$root_dir = dirname($dir);

$uri_path = '';
if (!empty($doc_root)) {
    if (stripos($root_dir, $doc_root) === 0) {
        $uri_path = substr($root_dir, strlen($doc_root));
    } else {
        $root_dir_lower = strtolower($root_dir);
        $doc_root_lower = strtolower($doc_root);
        if (stripos($root_dir_lower, $doc_root_lower) === 0) {
            $uri_path = substr($root_dir, strlen($doc_root));
        }
    }
}

if (empty($uri_path) || $uri_path === $root_dir) {
    $script_dir_clean = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if (strpos($script_dir_clean, '/pages') !== false) {
        $uri_path = str_replace('/pages', '', $script_dir_clean);
    } else {
        $uri_path = $script_dir_clean;
    }
}

$uri_path = '/' . ltrim(str_replace('\\', '/', $uri_path), '/');
$uri_path = rtrim($uri_path, '/') . '/';

define('BASE_URL', $uri_path);

date_default_timezone_set('Asia/Manila');

error_reporting(E_ALL);
ini_set('display_errors', 1);
