import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

const POPOVER_CLASS = 'labgoto-tour-popover';

const registry = {};
let activeTour = null;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function markComplete(config) {
    if (!config.completeUrl || config.completed) return;
    config.completed = true;

    fetch(config.completeUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': config.csrf,
            'Accept': 'application/json',
        },
    }).catch(() => {});
}

function buildSteps(rawSteps) {
    return (rawSteps || [])
        .map((step) => ({
            element: step.element || undefined,
            popover: {
                title: step.title || '',
                description: step.description || '',
                side: step.side || 'bottom',
                align: step.align || 'start',
            },
        }))
        .filter((step) => !step.element || document.querySelector(step.element));
}

export function startTour(key) {
    const config = registry[key];
    if (!config || activeTour) return;

    const steps = buildSteps(config.steps);
    if (!steps.length) {
        markComplete(config);
        return;
    }

    activeTour = driver({
        animate: !prefersReducedMotion(),
        allowClose: true,
        overlayColor: 'rgba(15, 23, 42, 0.75)',
        showProgress: true,
        progressText: '{{current}} of {{total}}',
        nextBtnText: 'Next',
        prevBtnText: 'Back',
        doneBtnText: 'Got it',
        popoverClass: POPOVER_CLASS,
        steps,
        onDestroyed: () => {
            activeTour = null;
            markComplete(config);
            document.dispatchEvent(new CustomEvent('labgoto:tour-finished', { detail: { key } }));
        },
    });

    activeTour.drive();
}

export function registerTours() {
    document.querySelectorAll('[data-tour-config]').forEach((el) => {
        const key = el.dataset.tourKey;
        if (!key || registry[key]) return;

        let steps = [];
        try {
            steps = JSON.parse(el.dataset.steps || '[]');
        } catch {
            steps = [];
        }

        registry[key] = {
            steps,
            completeUrl: el.dataset.completeUrl,
            csrf: el.dataset.csrf,
            completed: false,
        };

        const requested = new URLSearchParams(window.location.search).get('tour');
        if (requested === key) {
            setTimeout(() => startTour(key), 700);
            return;
        }

        if (el.dataset.autostart === 'true') {
            setTimeout(() => startTour(key), 700);
        }
    });
}

window.LabgotoTour = { start: startTour };
