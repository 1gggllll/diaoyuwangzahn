<?php
require __DIR__ . '/config.php';

$base = demo_base_url();
$link = $base . '/index.php?from=qrcode';
$staticQr = __DIR__ . '/img/qrcode-static.png';
if (file_exists($staticQr)) {
    $qrSrc = 'img/qrcode-static.png';
} else {
    $qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' . urlencode($link);
}
$linkSafe = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
$qrSafe = htmlspecialchars($qrSrc, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>扫码参与 · 英雄联盟</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="site site-qrcode">
<header class="site-header site-header-minimal">
    <div class="wrap header-inner">
        <a class="brand" href="index.php">
            <span class="brand-text"><strong>英雄联盟</strong></span>
        </a>
        <a class="link-back" href="index.php">返回首页</a>
    </div>
</header>

<main class="qrcode-main">
    <div class="qrcode-card">
        <header class="qrcode-head">
            <p class="eyebrow">限时活动</p>
            <h1>扫码参与活动</h1>
            <p>扫描下方二维码，即可进入活动页面领取限定礼包。</p>
        </header>
        <div class="qrcode-frame">
            <img src="<?= $qrSafe ?>" alt="活动二维码" width="240" height="240">
        </div>
        <p class="qrcode-url"><span>活动链接</span><?= $linkSafe ?></p>
    </div>
</main>

<footer class="site-footer site-footer-minimal">
    <div class="wrap">
        <p>© 2026 腾讯游戏 版权所有</p>
    </div>
</footer>
</body>
</html>
