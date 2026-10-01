# ADR 0002: Ultra-Lightweight Zero-Dependency Frontend Engine

- **Status**: Accepted
- **Confidence**: Verified
- **Date**: 2026-09-30
- **Category**: Structural Topology & Dependencies
- **Supersedes**: None
- **Superseded By**: None

## Context
Many WordPress slider/carousel plugins load bulky external JavaScript bundles (e.g. Slick Carousel + jQuery, Swiper 150KB+), degrading site loading speeds, Google PageSpeed scores, and Core Web Vitals (LCP, CLS, INP). The user explicitly requested an ultra-lightweight plugin that handles carousels, sliding animations, marquee tickers, and responsive grids.

## Decision
1. **No External JavaScript Libraries**: Refuse inclusion of jQuery slider plugins or large bundle dependencies.
2. **Carousel & Sliding**: Utilize modern CSS Scroll-Snap (`scroll-snap-type: x mandatory`) coupled with an ultra-compact (~2.5 KB) Vanilla JavaScript controller for touch swiping, navigation arrow triggering, and requestAnimationFrame autoplay.
3. **Continuous Ticker / Marquee**: Utilize 100% pure CSS `@keyframes` with hardware GPU acceleration (`transform: translate3d(...)`) and `will-change: transform`. Duplicate DOM elements cleanly in PHP to achieve infinite, seamless horizontal scrolling without JavaScript calculation overhead.
4. **Aspect Ratio Enforcement**: Use modern CSS `aspect-ratio` (`16/9`, `4/3`, `1/1`, `auto`) and `object-fit: contain` to preserve logo geometry without distortion regardless of canvas size.
5. **Asset Loading Budget**: Ensure total frontend assets remain under 10 KB (CSS < 6 KB, JS < 3 KB).

## Consequences
- Near-instantaneous asset downloads and zero render-blocking JavaScript.
- Full mobile responsiveness and native touch gesture fidelity across modern browsers (Chrome, Safari, Firefox, Edge).
- Older non-compliant browsers fall back gracefully to horizontal scroll or clean responsive grid.

## Evidence
- Core Web Vitals guidelines
- CSS Scroll Snap Module Level 1 specification
