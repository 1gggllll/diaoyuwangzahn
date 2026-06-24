<?php
session_start();
require __DIR__ . '/../config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('SELECT id FROM admins WHERE username = ? AND password = ?');
    $stmt->execute([$_POST['user'] ?? '', $_POST['pass'] ?? '']);
    if ($stmt->fetch()) {
        session_regenerate_id(true);
        $_SESSION['admin'] = 1;
        header('Location: index.php');
        exit;
    }
    $err = '账号或密码错误';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>教学后台登录</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
    <h2>后台管理登录</h2>
    <?php if ($err !== ''): ?>
        <p class="error"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <div class="card">
        <form method="post">
            <label>账号</label>
            <input type="text" name="user" value="admin">
            <label>密码</label>
            <input type="password" name="pass">
            <button type="submit" class="btn">登录</button>
        </form>
    </div>
</div>
</body>
</html>
