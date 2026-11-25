/**
 * GNN Logos - AAA Frontend Controller (Zero-Dependency Vanilla JS)
 * Handles Seamless Marquee Fill, Bug-Free Scroll-Snap Carousel, and Accessible Lightbox.
 *
 * @package GNN_Logos
 * @version 2.0.0
 */
(function () {
    'use strict';

    /* ==========================================================================
       1. Seamless Marquee Viewport Fill Engine
       ========================================================================== */
    function fillMarquee(wrapper) {
        var track = wrapper.querySelector('.gnn-marquee-track');
        if (!track) return;
        var primary = track.querySelector('.gnn-marquee-group:not([data-gnn-clone="1"])');
        if (!primary) return;

        var containerWidth = wrapper.clientWidth || window.innerWidth;
        var groupWidth = primary.getBoundingClientRect().width;
        if (!groupWidth || groupWidth < 10) return;

        // Need enough groups so that (totalGroups - 1) * groupWidth >= containerWidth + buffer
        var neededGroups = Math.max(2, Math.ceil(containerWidth / groupWidth) + 2);
        if (neededGroups > 12) neededGroups = 12;

        var existing = track.querySelectorAll('.gnn-marquee-group');
        var currentCount = existing.length;

        if (currentCount < neededGroups) {
            for (var i = currentCount; i < neededGroups; i++) {
                var clone = primary.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                clone.setAttribute('data-gnn-clone', '1');
                clone.querySelectorAll('a, button').forEach(function (el) {
                    el.setAttribute('tabindex', '-1');
                });
                track.appendChild(clone);
            }
        }
    }

    function initMarquees(root) {
        var marquees = root.querySelectorAll('.gnn-logos-layout-marquee');
        marquees.forEach(function (wrapper) {
            if (wrapper.getAttribute('data-gnn-init') === '1') {
                fillMarquee(wrapper);
                return;
            }
            wrapper.setAttribute('data-gnn-init', '1');
            fillMarquee(wrapper);

            if (typeof ResizeObserver !== 'undefined') {
                var ro = new ResizeObserver(function () {
                    fillMarquee(wrapper);
                });
                ro.observe(wrapper);
            }
        });
    }

    /* ==========================================================================
       2. Scroll-Snap Carousel Engine (Fixed Off-by-One, Smart Dots, Visibility)
       ========================================================================== */
    function initCarousels(root) {
        var carousels = root.querySelectorAll('.gnn-logos-layout-carousel');
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
            var isVisible = true;
            var isPausedByUser = false;

            function getStep() {
                var gap = parseFloat(window.getComputedStyle(viewport).gap) || 20;
                return slides[0].getBoundingClientRect().width + gap;
            }

            function getMaxScroll() {
                return Math.max(0, viewport.scrollWidth - viewport.clientWidth);
            }

            function syncLayoutState() {
                var max = getMaxScroll();
                var isStatic = max <= 4;
                carousel.classList.toggle('gnn-carousel-static', isStatic);

                if (dots.length) {
                    var step = getStep();
                    var stopCount = step > 0 ? Math.min(dots.length, Math.max(1, Math.round(max / step) + 1)) : 1;
                    dots.forEach(function (dot, idx) {
                        dot.hidden = idx >= stopCount;
                    });
                    syncActiveDot();
                }

                if (isStatic) {
                    stop();
                } else if (isAutoplay && isVisible && !isPausedByUser) {
                    start();
                }
            }

            function syncActiveDot() {
                if (!dots.length) return;
                var step = getStep();
                var max = getMaxScroll();
                var active = 0;
                if (step > 0 && max > 0) {
                    if (viewport.scrollLeft >= max - 5) {
                        active = Math.round(max / step);
                    } else {
                        active = Math.round(viewport.scrollLeft / step);
                    }
                }
                dots.forEach(function (d, idx) {
                    var isActive = idx === active;
                    d.classList.toggle('active', isActive);
                    d.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });
            }

            function goNext() {
                var max = getMaxScroll();
                if (max <= 4) return;
                var step = getStep();
                // Fixed off-by-one: wrap only when ALREADY at the end, never skip the last slide
                var target = (viewport.scrollLeft >= max - 5) ? 0 : Math.min(max, viewport.scrollLeft + step);
                viewport.scrollTo({ left: target, behavior: 'smooth' });
            }

            function goPrev() {
                var max = getMaxScroll();
                if (max <= 4) return;
                var step = getStep();
                var target = (viewport.scrollLeft <= 5) ? max : Math.max(0, viewport.scrollLeft - step);
                viewport.scrollTo({ left: target, behavior: 'smooth' });
            }

            if (next) next.addEventListener('click', function (e) { e.preventDefault(); goNext(); resetAutoplay(); });
            if (prev) prev.addEventListener('click', function (e) { e.preventDefault(); goPrev(); resetAutoplay(); });

            carousel.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    goNext();
                    resetAutoplay();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    goPrev();
                    resetAutoplay();
                }
            });

            if (dots.length) {
                dots.forEach(function (dot, i) {
                    dot.addEventListener('click', function (e) {
                        e.preventDefault();
                        var target = Math.min(getMaxScroll(), getStep() * i);
                        viewport.scrollTo({ left: target, behavior: 'smooth' });
                        resetAutoplay();
                    });
                });
                var ticking = false;
                viewport.addEventListener('scroll', function () {
                    if (!ticking) {
                        window.requestAnimationFrame(function () {
                            syncActiveDot();
                            ticking = false;
                        });
                        ticking = true;
                    }
                }, { passive: true });
            }

            function start() {
                if (isAutoplay && isVisible && !isPausedByUser && !timer && getMaxScroll() > 4 && !document.hidden) {
                    timer = setInterval(goNext, speed);
                }
            }

            function stop() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }

            function resetAutoplay() {
                if (timer) {
                    stop();
                    start();
                }
            }

            if (isAutoplay) {
                if (pause) {
                    carousel.addEventListener('mouseenter', function () { isPausedByUser = true; stop(); });
                    carousel.addEventListener('mouseleave', function () { isPausedByUser = false; start(); });
                    carousel.addEventListener('focusin', function () { isPausedByUser = true; stop(); });
                    carousel.addEventListener('focusout', function () { isPausedByUser = false; start(); });
                    carousel.addEventListener('touchstart', function () { isPausedByUser = true; stop(); }, { passive: true });
                    carousel.addEventListener('touchend', function () { isPausedByUser = false; start(); }, { passive: true });
                }

                document.addEventListener('visibilitychange', function () {
                    if (document.hidden) {
                        stop();
                    } else {
                        start();
                    }
                });

                if (typeof IntersectionObserver !== 'undefined') {
                    var io = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            isVisible = entry.isIntersecting;
                            if (isVisible) {
                                start();
                            } else {
                                stop();
                            }
                        });
                    }, { threshold: 0.1 });
                    io.observe(carousel);
                }
            }

            // Smooth drag-to-scroll using clientX + snap suspension
            var isDown = false, hasDragged = false, startX = 0, scrollStart = 0;
            viewport.addEventListener('mousedown', function (e) {
                if (e.button !== 0 || getMaxScroll() <= 4) return;
                isDown = true;
                hasDragged = false;
                startX = e.clientX;
                scrollStart = viewport.scrollLeft;
                viewport.classList.add('is-dragging');
            });

            window.addEventListener('mouseup', function () {
                if (!isDown) return;
                isDown = false;
                viewport.classList.remove('is-dragging');
            });

            window.addEventListener('mousemove', function (e) {
                if (!isDown) return;
                var delta = e.clientX - startX;
                if (Math.abs(delta) > 5) {
                    hasDragged = true;
                }
                e.preventDefault();
                viewport.scrollLeft = scrollStart - delta * 1.25;
            });

            viewport.addEventListener('click', function (e) {
                if (hasDragged) {
                    e.preventDefault();
                    e.stopPropagation();
                    hasDragged = false;
                }
            }, true);

            syncLayoutState();
            if (typeof ResizeObserver !== 'undefined') {
                var ro = new ResizeObserver(syncLayoutState);
                ro.observe(viewport);
            }
        });
    }

    /* ==========================================================================
       3. Accessible Zero-Dependency Certificate / Logo Lightbox
       ========================================================================== */
    var lightboxEl = null;
    var lastTrigger = null;

    function getLightbox() {
        if (lightboxEl) return lightboxEl;

        lightboxEl = document.createElement('div');
        lightboxEl.className = 'gnn-lightbox';
        lightboxEl.setAttribute('role', 'dialog');
        lightboxEl.setAttribute('aria-modal', 'true');
        lightboxEl.setAttribute('aria-labelledby', 'gnn-lb-title');

        var dialog = document.createElement('div');
        dialog.className = 'gnn-lightbox-dialog';

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'gnn-lightbox-close';
        closeBtn.setAttribute('aria-label', 'Kapat');
        closeBtn.textContent = '\u00d7';

        var media = document.createElement('div');
        media.className = 'gnn-lightbox-media';
        var img = document.createElement('img');
        img.className = 'gnn-lightbox-img';
        img.alt = '';
        media.appendChild(img);

        var title = document.createElement('h3');
        title.id = 'gnn-lb-title';
        title.className = 'gnn-lightbox-title';

        var desc = document.createElement('p');
        desc.className = 'gnn-lightbox-desc';

        var badges = document.createElement('div');
        badges.className = 'gnn-lightbox-badges';

        var link = document.createElement('a');
        link.className = 'gnn-lightbox-link';
        link.rel = 'noopener noreferrer';

        dialog.appendChild(closeBtn);
        dialog.appendChild(media);
        dialog.appendChild(title);
        dialog.appendChild(desc);
        dialog.appendChild(badges);
        dialog.appendChild(link);
        lightboxEl.appendChild(dialog);
        document.body.appendChild(lightboxEl);

        closeBtn.addEventListener('click', closeLightbox);
        lightboxEl.addEventListener('click', function (e) {
            if (e.target === lightboxEl) closeLightbox();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && lightboxEl && lightboxEl.classList.contains('is-open')) {
                closeLightbox();
            }
        });

        return lightboxEl;
    }

    function openLightbox(trigger) {
        var lb = getLightbox();
        lastTrigger = trigger;

        var imgSrc = trigger.getAttribute('data-lb-img') || '';
        var titleText = trigger.getAttribute('data-lb-title') || '';
        var descText = trigger.getAttribute('data-lb-desc') || '';
        var codesRaw = trigger.getAttribute('data-lb-codes') || '';
        var href = trigger.getAttribute('data-lb-url') || '';
        var target = trigger.getAttribute('data-lb-target') || '_blank';

        var imgEl = lb.querySelector('.gnn-lightbox-img');
        var mediaEl = lb.querySelector('.gnn-lightbox-media');
        var titleEl = lb.querySelector('.gnn-lightbox-title');
        var descEl = lb.querySelector('.gnn-lightbox-desc');
        var badgesEl = lb.querySelector('.gnn-lightbox-badges');
        var linkEl = lb.querySelector('.gnn-lightbox-link');

        if (imgSrc) {
            imgEl.src = imgSrc;
            imgEl.alt = titleText;
            mediaEl.style.display = '';
        } else {
            imgEl.removeAttribute('src');
            mediaEl.style.display = 'none';
        }

        titleEl.textContent = titleText;
        titleEl.style.display = titleText ? '' : 'none';

        descEl.textContent = descText;
        descEl.style.display = descText ? '' : 'none';

        while (badgesEl.firstChild) badgesEl.removeChild(badgesEl.firstChild);
        if (codesRaw) {
            var codes = codesRaw.split('||').filter(Boolean);
            codes.forEach(function (code) {
                var span = document.createElement('span');
                span.className = 'gnn-lightbox-badge';
                span.textContent = code;
                badgesEl.appendChild(span);
            });
            badgesEl.style.display = codes.length ? '' : 'none';
        } else {
            badgesEl.style.display = 'none';
        }

        if (href && /^https?:\/\//i.test(href)) {
            linkEl.href = href;
            linkEl.target = target === '_self' ? '_self' : '_blank';
            linkEl.textContent = trigger.getAttribute('data-lb-link-label') || 'Belgeyi / Sayfayı Aç';
            linkEl.style.display = '';
        } else {
            linkEl.removeAttribute('href');
            linkEl.style.display = 'none';
        }

        lb.classList.add('is-open');
        document.documentElement.style.overflow = 'hidden';
        lb.querySelector('.gnn-lightbox-close').focus();
    }

    function closeLightbox() {
        if (!lightboxEl) return;
        lightboxEl.classList.remove('is-open');
        document.documentElement.style.overflow = '';
        if (lastTrigger && typeof lastTrigger.focus === 'function') {
            lastTrigger.focus();
        }
        lastTrigger = null;
    }

    var lightboxDelegated = false;

    function initLightbox() {
        if (lightboxDelegated) return;
        lightboxDelegated = true;
        document.addEventListener('click', function (e) {
            var trigger = e.target && e.target.closest ? e.target.closest('.gnn-logo-item[data-gnn-lightbox="1"]') : null;
            if (!trigger) return;
            e.preventDefault();
            openLightbox(trigger);
        });
    }

    function initAll(root) {
        var scope = root || document;
        initMarquees(scope);
        initCarousels(scope);
        initLightbox();
    }

    window.GNNLogosInit = initAll;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initAll(document); });
    } else {
        initAll(document);
    }
})();
