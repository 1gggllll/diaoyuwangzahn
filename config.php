<?php
$host = '127.0.0.1';
$db   = 'phishguard';
$user = 'root';
$pass = '';

if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

/** 手机扫码等场景使用的公网/局域网根地址，如 http://192.168.1.5:8080/phishguard */
$demo_base = $demo_base ?? '';

function demo_base_url(): string {
    global $demo_base;

    if ($demo_base !== '') {
        return rtrim($demo_base, '/');
    }

    $lanFile = __DIR__ . '/lan-url.txt';
    if (is_file($lanFile)) {
        $fromFile = trim((string) file_get_contents($lanFile));
        if ($fromFile !== '') {
            return rtrim($fromFile, '/');
        }
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $path = rtrim(dirname($_SERVER['PHP_SELF'] ?? '/phishguard'), '/\\');
    return $scheme . '://' . $host . $path;
}

function db_connect($password) {
    global $host, $db, $user;
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function db() {
    global $pass;
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $candidates = array_unique(array_merge(
        [$pass],
        ['', '123456', 'root', 'mysql']
    ));

    $last = null;
    foreach ($candidates as $candidate) {
        try {
            $pdo = db_connect($candidate);
            return $pdo;
        } catch (PDOException $e) {
            $last = $e;
        }
    }

    throw $last ?? new PDOException('无法连接 MySQL，请检查服务并配置 config.local.php');
}
