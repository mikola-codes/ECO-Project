
        document.addEventListener('DOMContentLoaded', () => {
            gsap.registerPlugin(ScrollTrigger);

            // Initialize Lenis smooth scroll for butter-smooth mouse wheel experience
            const lenis = new Lenis({
                duration: 1.5,
                easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
                smoothWheel: true,
                wheelMultiplier: 1.0,
            });

            lenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((time) => { lenis.raf(time * 1000) });
            gsap.ticker.lagSmoothing(0);
            
            const isMobile = window.innerWidth < 992;
            
            // Initial states
            gsap.set("#device-img", { scale: 1.4, xPercent: -50, yPercent: -50, rotationX: 0, rotationY: 0, rotationZ: 0, force3D: true, z: 0.1 });
            gsap.set(".scroll-step", { opacity: 0, y: 40 });
            gsap.set("#step-1", { opacity: 1, y: 0 }); // First step visible
            document.getElementById('step-1').classList.add('active');

            // 3. The main timeline tied to the scroll-track
            const tl = gsap.timeline({
                scrollTrigger: {
                    trigger: "#scroll-track",
                    pin: ".sticky-view", // Use GSAP pin instead of CSS sticky to prevent hardware jitter
                    start: "top top",
                    end: "bottom bottom",
                    scrub: 2 // High scrub value to glide past chunky mouse wheel clicks
                }
            });

            // Transition 1 -> 2
            tl.to("#step-1", { opacity: 0, y: -40, duration: 1, onComplete: () => document.getElementById('step-1').classList.remove('active') })
              .to("#device-img", { 
                  scale: 1.5,
                  xPercent: -10,
                  yPercent: -50,
                  rotationX: 0,
                  rotationY: 0,
                  rotationZ: 0,
                  duration: 2,
                  ease: "power2.inOut"
              }, "<")
              .to("#step-2", { opacity: 1, y: 0, duration: 1, onStart: () => document.getElementById('step-2').classList.add('active') }, "-=1");

            // Transition 2 -> 3
            tl.to("#step-2", { opacity: 0, y: -40, duration: 1, onComplete: () => document.getElementById('step-2').classList.remove('active') }, "+=0.5")
              .to("#device-img", { 
                  scale: 1.5,
                  xPercent: -90,
                  yPercent: -50,
                  rotationX: 0,
                  rotationY: 0,
                  rotationZ: 0,
                  duration: 2,
                  ease: "power2.inOut"
              }, "<")
              .to("#step-3", { opacity: 1, y: 0, duration: 1, onStart: () => document.getElementById('step-3').classList.add('active') }, "-=1");

            // Transition 3 -> 4
            tl.to("#step-3", { opacity: 0, y: -40, duration: 1, onComplete: () => document.getElementById('step-3').classList.remove('active') }, "+=0.5")
              .to("#device-img", { 
                  scale: 2.8,
                  xPercent: -50,
                  yPercent: -50,
                  rotationY: 0, 
                  rotationX: 0,
                  rotationZ: 0,
                  opacity: 0,
                  filter: "blur(20px)",
                  duration: 2,
                  ease: "power2.inOut"
              }, "<")
              .to("#step-4", { opacity: 1, y: 0, duration: 1, onStart: () => document.getElementById('step-4').classList.add('active') }, "-=1");

            // History Section Reveal Observer
            const historySec = document.querySelector('.history-section');
            if(historySec) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if(entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target); // Play once
                        }
                    });
                }, { threshold: 0.3 }); // Trigger when 30% of the section is visible
                observer.observe(historySec);
            }

            // Bento Spotlight
            const bentoBoxes = document.querySelectorAll('.bento-box');
            bentoBoxes.forEach(box => {
                box.addEventListener('mousemove', (e) => {
                    const rect = box.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    box.style.setProperty('--x', `${x}px`);
                    box.style.setProperty('--y', `${y}px`);
                });
            });

            // FAQ Logic
            const faqs = document.querySelectorAll('.faq-item');
            faqs.forEach(faq => {
                faq.addEventListener('click', () => {
                    const isActive = faq.classList.contains('active');
                    faqs.forEach(f => f.classList.remove('active'));
                    if (!isActive) faq.classList.add('active');
                });
            });
            // (FAQ Logic was duplicated here, now removed)
        });

        // Theme Toggle Logic (moved outside DOMContentLoaded for instant execution)
        const html = document.documentElement;
        const themeBtn = document.getElementById('themeToggle');
        const themeIconEl = document.getElementById('themeIcon');
        
        let currentTheme = localStorage.getItem('eco-theme') || 'light';
        if (currentTheme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            if (themeIconEl) themeIconEl.innerHTML = '&#x2600;&#xFE0F;';
        }
        
        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                if (html.getAttribute('data-theme') === 'dark') {
                    html.removeAttribute('data-theme');
                    localStorage.setItem('eco-theme', 'light');
                    if (themeIconEl) themeIconEl.innerHTML = '&#x1F319;';
                } else {
                    html.setAttribute('data-theme', 'dark');
                    localStorage.setItem('eco-theme', 'dark');
                    if (themeIconEl) themeIconEl.innerHTML = '&#x2600;&#xFE0F;';
                }
            });
        }
    
