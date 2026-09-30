/**
 * GNN Logos - Frontend Controller (Ultra-light Vanilla JS, Zero-dependency)
 * @package GNN_Logos
 * @version 1.0.0
 */
(function () {
    'use strict';
    function init() {
        var carousels = document.querySelectorAll('.gnn-logos-layout-carousel');
        carousels.forEach(function (carousel) {
            if (carousel.getAttribute('data-gnn-init') === '1') return;
            carousel.setAttribute('data-gnn-init', '1');

            var viewport = carousel.querySelector('.gnn-carousel-viewport');
            if (!viewport) return;
            var slides = viewport.querySelectorAll('.gnn-carousel-slide');
            if (!slides || !slides.length) return;

            var prev = carousel.querySelector('.gnn-carousel-arrow.prev');
            var next = carousel.querySelector('.gnn-carousel-arrow.next');
            var dots = carousel.querySelectorAll('.gnn-carousel-dot');

            var isAutoplay = carousel.getAttribute('data-autoplay') === 'true';
            var speed = parseInt(carousel.getAttribute('data-autoplay-speed'), 10) || 3000;
            var pause = carousel.getAttribute('data-pause-on-hover') !== 'false';
            var timer = null;

            function getStep() {
                var gap = parseFloat(window.getComputedStyle(viewport).gap) || 20;
                return slides[0].getBoundingClientRect().width + gap;
            }

            function goNext() {
                var max = viewport.scrollWidth - viewport.clientWidth;
                var step = getStep();
                viewport.scrollTo({ left: (viewport.scrollLeft + step >= max - 5) ? 0 : viewport.scrollLeft + step, behavior: 'smooth' });
            }

            function goPrev() {
                var max = viewport.scrollWidth - viewport.clientWidth;
                var step = getStep();
                viewport.scrollTo({ left: (viewport.scrollLeft <= 5) ? max : viewport.scrollLeft - step, behavior: 'smooth' });
            }

            if (next) next.addEventListener('click', function (e) { e.preventDefault(); goNext(); });
            if (prev) prev.addEventListener('click', function (e) { e.preventDefault(); goPrev(); });

            if (dots.length) {
                dots.forEach(function (dot, i) {
                    dot.addEventListener('click', function (e) {
                        e.preventDefault();
                        viewport.scrollTo({ left: getStep() * i, behavior: 'smooth' });
                    });
                });
                var ticking = false;
                viewport.addEventListener('scroll', function () {
                    if (!ticking) {
                        window.requestAnimationFrame(function () {
                            var active = Math.round(viewport.scrollLeft / getStep());
                            dots.forEach(function (d, idx) { d.classList.toggle('active', idx === active); });
                            ticking = false;
                        });
                        ticking = true;
                    }
                }, { passive: true });
            }

            function start() { if (isAutoplay && !timer) timer = setInterval(goNext, speed); }
            function stop() { if (timer) { clearInterval(timer); timer = null; } }

            if (isAutoplay) {
                start();
                if (pause) {
                    carousel.addEventListener('mouseenter', stop);
                    carousel.addEventListener('mouseleave', start);
                    carousel.addEventListener('touchstart', stop, { passive: true });
                    carousel.addEventListener('touchend', start, { passive: true });
                }
            }

            var isDown = false, startX = 0, scrollStart = 0;
            viewport.addEventListener('mousedown', function (e) {
                isDown = true;
                viewport.style.scrollBehavior = 'auto';
                startX = e.pageX - viewport.offsetLeft;
                scrollStart = viewport.scrollLeft;
            });
            window.addEventListener('mouseup', function () {
                if (!isDown) return;
                isDown = false;
                viewport.style.scrollBehavior = 'smooth';
            });
            viewport.addEventListener('mousemove', function (e) {
                if (!isDown) return;
                e.preventDefault();
                viewport.scrollLeft = scrollStart - (e.pageX - viewport.offsetLeft - startX) * 1.5;
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
