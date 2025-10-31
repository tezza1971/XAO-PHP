<?php
// public/index.php

define('PROJECT_ROOT', dirname(__DIR__));
require_once PROJECT_ROOT . '/vendor/autoload.php';

// A simple router to handle the demo applications.
$request_uri = $_SERVER['REQUEST_URI'];
$request_path = parse_url($request_uri, PHP_URL_PATH);

$demo_path = PROJECT_ROOT . '/public' . $request_path;

if (file_exists($demo_path) && is_file($demo_path)) {
    // If the file exists, serve it.
    // This is for the .html and .php files in the demo directories.
    if (str_ends_with($demo_path, '.php')) {
        // Execute PHP files.
        require $demo_path;
    } else {
        // Serve other files as-is.
        return false;
    }
} else {
    // If the file doesn't exist, show a 404.
    http_response_code(404);
    echo "404 Not Found";
}
