<?php
// PHP 内置服务器路由：模拟 Apache Alias /phishguard
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$prefix = '/phishguard';

if (strpos($uri, $prefix) === 0) {
    $path = substr($uri, strlen($prefix));
    if ($path === '' || $path === '/') {
        $path = '/index.php';
    }
    $path = urldecode($path);
    $file = __DIR__ . $path;
    if (is_file($file)) {
        chdir(__DIR__);
        if (str_ends_with(strtolower($file), '.php')) {
            require $file;
            return true;
        }

        $types = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff2' => 'font/woff2',
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($types[$ext])) {
            header('Content-Type: ' . $types[$ext]);
        }
        readfile($file);
        return true;
    }
}

http_response_code(404);
echo '404 Not Found';
return true;
