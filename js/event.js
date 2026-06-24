/**
 * Event pages: countdown, live participant counter, and carousel.
 */
(function () {
    'use strict';

    // Carousel
    var slides = document.querySelectorAll('.carousel-slide');
    if (slides.length > 1) {
        var current = 0;
        setInterval(function () {
            slides[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].classList.add('active');
        }, 4000);
    }

    // Countdown timer
    var timerEl = document.getElementById('timer');
    if (timerEl) {
        var total = 47 * 3600 + 59 * 60 + 59;
        function pad(n) { return String(n).padStart(2, '0'); }
        function render() {
            var h = Math.floor(total / 3600);
            var m = Math.floor((total % 3600) / 60);
            var s = total % 60;
            var html =
                '<div class="countdown-unit"><span class="countdown-num">' + pad(h) + '</span><span class="countdown-label">时</span></div>' +
                '<span class="countdown-colon">:</span>' +
                '<div class="countdown-unit"><span class="countdown-num">' + pad(m) + '</span><span class="countdown-label">分</span></div>' +
                '<span class="countdown-colon">:</span>' +
                '<div class="countdown-unit"><span class="countdown-num">' + pad(s) + '</span><span class="countdown-label">秒</span></div>';
            timerEl.innerHTML = html;
        }
        render();
        setInterval(function () {
            if (total <= 0) return;
            total--;
            render();
        }, 1000);
    }

    // Live participant counter
    var numEl = document.getElementById('live-num');
    if (numEl) {
        var n = 12847;
        setInterval(function () {
            n += Math.floor(Math.random() * 3) + 1;
            numEl.textContent = n.toLocaleString('zh-CN');
        }, 5000);
    }
})();
