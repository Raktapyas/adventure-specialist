import Alpine from 'alpinejs';

Alpine.start();

window.Alpine = Alpine;

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function splitReveal(el) {
    const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
    const textNodes = [];
    while (walker.nextNode()) {
        const node = walker.currentNode;
        if (node.textContent.trim().length > 0) textNodes.push(node);
    }

    textNodes.forEach((node) => {
        const words = node.textContent.trim().split(/\s+/);
        if (words.length < 2) return;

        const fragment = document.createDocumentFragment();
        words.forEach((word, index) => {
            const span = document.createElement('span');
            span.className = 'ts-word';
            span.textContent = word;
            span.style.transitionDelay = `${index * 45}ms`;
            fragment.appendChild(span);
            if (index < words.length - 1) fragment.appendChild(document.createTextNode(' '));
        });
        node.replaceWith(fragment);
    });
}

function animateCount(el) {
    const target = parseFloat(el.dataset.count);
    if (Number.isNaN(target)) return;
    const duration = 2000;
    const start = performance.now();
    const suffix = el.dataset.suffix ?? '';

    const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = `${Math.floor(target * eased)}${suffix}`;
        if (progress < 1) requestAnimationFrame(tick);
    };

    requestAnimationFrame(tick);
}

if (prefersReducedMotion) {
    document.querySelectorAll('.reveal, .reveal-rise, .reveal-mask, .split-reveal').forEach((el) =>
        el.classList.add('is-visible'),
    );
    document.querySelectorAll('[data-count]').forEach((el) => {
        el.textContent = `${el.dataset.count}${el.dataset.suffix ?? ''}`;
    });
} else {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    document.querySelectorAll('.reveal, .reveal-rise, .reveal-mask').forEach((el) => observer.observe(el));

    document.querySelectorAll('.split-reveal').forEach((el) => {
        splitReveal(el);
        observer.observe(el);
    });

    const counterObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCount(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.4 },
    );

    document.querySelectorAll('[data-count]').forEach((el) => counterObserver.observe(el));
}
