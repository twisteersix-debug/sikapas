<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // serve file langsung
}

// Default routing
if ($path === '/' || $path === '') {
    require __DIR__ . '/index.php';
} else {
    $phpFile = __DIR__ . $path;
    if (file_exists($phpFile)) {
        require $phpFile;
    } else {
        http_response_code(404);
        echo "Not Found";
    }
}
