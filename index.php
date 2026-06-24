<?php
session_start();

$allowed = ['direct', 'url', 'qrcode'];
$from = $_GET['from'] ?? 'direct';
if (!in_array($from, $allowed, true)) {
    $from = 'direct';
}
$_SESSION['channel'] = $from;
$fromSafe = htmlspecialchars($from, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="英雄联盟 — 登录领取限定福利礼包">
    <title>限定福利 · 英雄联盟官方网站</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="site site-home">
<header class="site-header">
    <div class="wrap header-inner">
        <a class="brand" href="index.php">
            <svg class="brand-mark" width="36" height="36" viewBox="0 0 36 36" aria-hidden="true">
                <defs><linearGradient id="bm" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#d4af37"/><stop offset="100%" stop-color="#8b6914"/></linearGradient></defs>
                <path fill="url(#bm)" d="M18 2L4 10v16l14 8 14-8V10L18 2zm0 4.5l9.5 5.5V24L18 29.5 8.5 24V12L18 6.5z"/>
            </svg>
            <span class="brand-text">
                <strong>英雄联盟</strong>
                <small>LEAGUE OF LEGENDS</small>
            </span>
        </a>
        <nav class="site-nav" aria-label="主导航">
            <a href="index.php" class="active">首页</a>
            <a href="#rewards">活动奖励</a>
            <a href="#rules">活动规则</a>
        </nav>
        <a class="btn btn-sm btn-ghost" href="login.php?from=<?= $fromSafe ?>">账号登录</a>
    </div>
</header>

<main>
    <section class="hero">
        <div class="hero-carousel" aria-hidden="true">
            <img src="img/主页封面1.jpg" alt="封面1" class="carousel-slide active">
            <img src="img/主页封面2.jpg" alt="封面2" class="carousel-slide">
            <img src="img/主页封面3.jpg" alt="封面3" class="carousel-slide">
        </div>
        <div class="wrap hero-grid">
            <div class="hero-copy">
                <p class="eyebrow">英雄联盟 · 限时活动</p>
                <h1>限定福利礼包</h1>
                <p class="lead">限时登录领取专属福利礼包，活动期间每位召唤师仅限领取一次，礼包将在验证成功后发放至游戏内邮箱。</p>

                <div class="countdown-block">
                    <p class="countdown-title">距离活动结束还剩</p>
                    <div class="countdown" id="timer" aria-live="polite">47:59:59</div>
                </div>

                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="login.php?from=<?= $fromSafe ?>">立即领取</a>
                    <p class="hero-meta">已有 <span id="live-num">12,847</span> 位召唤师成功领取</p>
                </div>
            </div>
            <aside class="hero-preview">
                <div class="mail-ui">
                    <header class="mail-ui-head">
                        <span class="mail-ui-tag">限定福利</span>
                        <h3>限时专属礼包</h3>
                        <p>登录即领，限时发放，错过再等一年</p>
                    </header>
                    <ul class="mail-ui-items">
                        <li class="item-slot item-ssr">
                            <img src="img/点券.jpg" alt="点券" class="item-img">
                            <span class="item-name">点券</span>
                            <span class="item-qty">×6480</span>
                        </li>
                        <li class="item-slot item-sr">
                            <img src="img/龙年限定皮肤.jpg" alt="龙年限定皮肤" class="item-img">
                            <span class="item-name">龙年限定皮肤</span>
                            <span class="item-qty">永久</span>
                        </li>
                        <li class="item-slot item-sr">
                            <img src="img/传说级皮肤.jpg" alt="传说级皮肤" class="item-img">
                            <span class="item-name">传说级皮肤</span>
                            <span class="item-qty">永久</span>
                        </li>
                        <li class="item-slot item-r">
                            <img src="img/海克斯科技宝箱.jpg" alt="海克斯宝箱" class="item-img">
                            <span class="item-name">海克斯宝箱</span>
                            <span class="item-qty">×10</span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>

    <section class="section rewards-section" id="rewards" style="background-image: url('img/活动奖励1.jpg'); background-size: cover; background-position: center; position: relative;">
        <div style="position: absolute; inset: 0; background: rgba(15, 22, 32, 0.85);"></div>
        <div class="wrap" style="position: relative; z-index: 1;">
            <header class="section-head">
                <h2>活动奖励一览</h2>
                <p>登录成功后以下道具将一次性发放</p>
            </header>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>奖励名称</th>
                            <th>类型</th>
                            <th>数量</th>
                            <th>说明</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="tier tier-ssr">SSR</span> 点券</td>
                            <td>货币</td>
                            <td>6480</td>
                            <td>登录即领，不可交易</td>
                        </tr>
                        <tr>
                            <td><span class="tier tier-sr">SR</span> 龙年限定皮肤</td>
                            <td>外观</td>
                            <td>永久</td>
                            <td>龙年限定英雄皮肤</td>
                        </tr>
                        <tr>
                            <td><span class="tier tier-sr">SR</span> 传说级皮肤</td>
                            <td>外观</td>
                            <td>永久</td>
                            <td>传说品质英雄皮肤</td>
                        </tr>
                        <tr>
                            <td><span class="tier tier-r">R</span> 海克斯宝箱</td>
                            <td>道具</td>
                            <td>×10</td>
                            <td>开启可获得皮肤碎片</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="section-cta"><a class="btn btn-primary" href="login.php?from=<?= $fromSafe ?>">立即领取</a></p>
        </div>
    </section>

    <section class="section rules-section" id="rules">
        <div class="wrap">
            <header class="section-head">
                <h2>活动规则</h2>
            </header>
            <div class="rules-grid">
                <article class="rule-card">
                    <h3>活动时间</h3>
                    <p>即日起至活动倒计时结束，全服冒险者均可参与一次。</p>
                </article>
                <article class="rule-card">
                    <h3>参与条件</h3>
                    <p>需使用已注册的游戏账号登录，每个账号限领一次。</p>
                </article>
                <article class="rule-card">
                    <h3>发放说明</h3>
                    <p>奖励将在验证成功后 5 分钟内发放至游戏内邮箱，请及时领取。</p>
                </article>
                <article class="rule-card">
                    <h3>注意事项</h3>
                    <p>请在官方渠道参与活动，谨防仿冒页面骗取账号信息。</p>
                </article>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="wrap footer-grid">
        <div class="footer-brand">
            <strong>英雄联盟</strong>
            <p>© 2026 腾讯游戏 版权所有</p>
        </div>
        <div class="footer-links">
            <a href="index.php">官方网站</a>
            <a href="login.php">账号登录</a>
            <a href="qrcode.php">扫码下载</a>
        </div>
        <div class="footer-legal">
            <p>晋ICP备xxxxxx号 · 网络游戏行业防沉迷自律公约</p>
            <p>抵制不良游戏，拒绝盗版游戏。注意自我保护，谨防受骗上当。</p>
        </div>
    </div>
</footer>
<script src="js/event.js"></script>
</body>
</html>
