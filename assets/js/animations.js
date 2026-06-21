/**
 * UI-UX Pro Max Animations
 * Smooth scroll-triggered animations using IntersectionObserver
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Set up Intersection Observer for elements fading in on scroll
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const scrollObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // 2. Select all target elements and observe them
    const targets = document.querySelectorAll(`
        .feat-card, 
        .hiw-card, 
        .stat-bento, 
        .cta-card, 
        .hero-content, 
        .hero-visual,
        .section-card,
        .bento-grid > div
    `);
    
    targets.forEach(target => {
        scrollObserver.observe(target);
    });
    
    // 3. Immediately show hero elements if already in view (fallback)
    setTimeout(() => {
        const heroElems = document.querySelectorAll('.hero-content, .hero-visual');
        heroElems.forEach(el => el.classList.add('is-visible'));
    }, 100);
});
