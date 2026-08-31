import { Chart } from 'chart.js';

const DEFAULT_HEIGHT = 180;

function theme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function axisColors() {
    const dark = theme() === 'dark';
    return {
        grid: dark ? 'rgba(148,163,184,0.1)' : 'rgba(148,163,184,0.2)',
        text: dark ? '#94a3b8' : '#64748b',
    };
}

function baseOptions(kind, { suffix = '', min, max, stepSize, maxTicksLimit, legend = false, cutout } = {}) {
    const { grid, text } = axisColors();
    const fill = kind === 'line';

    const options = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 500 },
        plugins: {},
        scales: {},
    };

    if (legend) {
        options.plugins.legend = {
            position: 'bottom',
            labels: { color: text, font: { size: 11 }, padding: 12, usePointStyle: true, pointStyleWidth: 8 },
        };
    }

    if (kind !== 'doughnut') {
        const yTicks = { color: text, font: { size: 11 } };
        if (suffix) yTicks.callback = (v) => v + suffix;
        if (stepSize) yTicks.stepSize = stepSize;

        const y = { grid: { color: grid } };
        if (min !== undefined) y.min = min;
        if (max !== undefined) y.max = max;
        y.ticks = yTicks;

        options.scales = {
            x: { grid: { display: false }, ticks: { color: text, maxTicksLimit: maxTicksLimit || 8, font: { size: 11 } } },
            y,
        };
    }

    if (kind === 'doughnut') {
        options.cutout = cutout || '62%';
    }

    return options;
}

function buildDataset(kind, raw) {
    const palette = {
        indigo: { bg: 'rgba(99,102,241,0.5)', border: '#6366f1' },
        cyan: { bg: 'rgba(6,182,212,0.12)', border: '#06b6d4' },
        violet: { bg: 'rgba(139,92,246,0.12)', border: '#8b5cf6' },
        amber: { bg: 'rgba(245,158,11,0.55)', border: '#f59e0b' },
        emerald: { bg: 'rgba(16,185,129,0.55)', border: '#10b981' },
    };

    const p = palette[raw.color] || palette.indigo;

    const base = {
        label: raw.label || '',
        data: raw.data,
    };

    if (kind === 'doughnut') {
        return {
            ...base,
            backgroundColor: raw.colors || ['#22c55e', '#3b82f6', '#94a3b8'],
            borderWidth: 0,
            hoverOffset: 4,
        };
    }

    if (kind === 'bar') {
        return {
            ...base,
            backgroundColor: raw.backgroundColor || p.bg,
            borderColor: raw.borderColor || p.border,
            borderWidth: 1,
            borderRadius: 3,
            maxBarThickness: typeof raw.maxBarThickness === 'number' ? raw.maxBarThickness : 20,
        };
    }

    if (kind === 'line') {
        return {
            ...base,
            borderColor: raw.borderColor || p.border,
            backgroundColor: raw.backgroundColor || p.bg,
            fill: raw.fill !== false,
            tension: typeof raw.tension === 'number' ? raw.tension : 0.3,
            pointRadius: typeof raw.pointRadius === 'number' ? raw.pointRadius : 0,
            pointHitRadius: 10,
            borderWidth: 2,
            spanGaps: raw.spanGaps !== false,
        };
    }

    return base;
}

function render(canvas) {
    if (!canvas || !window.Chart) return;

    let config;
    try {
        config = JSON.parse(canvas.dataset.config || '{}');
    } catch (error) {
        return;
    }

    const kind = config.kind || 'bar';
    const datasets = (config.datasets || []).map((d) => buildDataset(kind, d));

    const instance = new Chart(canvas.getContext('2d'), {
        type: kind,
        data: { labels: config.labels || [], datasets },
        options: baseOptions(kind, config.options || {}),
    });

    canvas.__chart = instance;
}

function destroyAll() {
    document.querySelectorAll('canvas[data-config]').forEach((canvas) => {
        if (canvas.__chart) {
            canvas.__chart.destroy();
            canvas.__chart = null;
        }
    });
}

function renderAll() {
    destroyAll();
    document.querySelectorAll('canvas[data-config]').forEach((canvas) => {
        const height = canvas.dataset.height || DEFAULT_HEIGHT;
        canvas.style.height = `${height}px`;
        render(canvas);
    });
}

function init() {
    window.addEventListener('charts:render', () => renderAll());

    const observer = new MutationObserver(() => {
        if (document.documentElement.classList.contains('dark')) {
            renderAll();
        }
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderAll);
    } else {
        renderAll();
    }
}

export { init as initCharts, renderAll as renderCharts };
