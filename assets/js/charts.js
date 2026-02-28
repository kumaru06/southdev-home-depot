/**
 * SouthDev Home Depot – Charts (defensive version)
 * Restores retry helpers, health checks and helpful debug logs that were
 * present before the "cleanup" change. This file intentionally includes
 * gentle retries and layout handling to make the dashboard resilient to
 * slow CDN loads, font loading, and layout timing issues.
 */

(function () {
    'use strict';

    var DEBUG = true;

    function dlog() {
        if (!DEBUG) return;
        try { console.debug.apply(console, arguments); } catch (e) {}
    }
    function ilog() {
        if (!DEBUG) return;
        try { console.info.apply(console, arguments); } catch (e) {}
    }

    var COLORS = {
        primary: '#0B3D91',
        primaryLight: 'rgba(11,61,145,.14)',
        accent: '#FF6B00',
        accentLight: 'rgba(255,107,0,.16)',
        charcoal: '#2C3E50',
        steel: '#51606F',
        grid: 'rgba(0,0,0,.06)',
        palette: ['#0B3D91','#FF6B00','#2E7D32','#1565C0','#F59E0B','#51606F','#6A1B9A','#00838F']
    };

    var FONT = { family: "'Segoe UI Symbol','Inter','Segoe UI',sans-serif", size: 12, weight: 500 };

    var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 600, easing: 'easeOutQuart' },
        layout: { padding: { left: 28, right: 28 } },
        plugins: {
            legend: { labels: { font: FONT, padding: 12, usePointStyle: true } },
            tooltip: { backgroundColor: COLORS.charcoal, titleFont: { family: FONT.family, size: 13, weight: 700 }, bodyFont: { family: FONT.family, size: 12 }, padding: 10, cornerRadius: 6 }
        }
    };

    // Sales chart
    function initSalesChart(canvasId, labels, data) {
        var el = document.getElementById(canvasId);
        if (!el) return null;
        try {
            ilog('Initializing sales chart', canvasId, labels && labels.length || 0);

            // If labels look like monthly labels (contain a 4-digit year), use a time scale
            var useTimeScale = Array.isArray(labels) && labels.length > 0 && /\d{4}/.test(String(labels[0]));
            if (useTimeScale) {
                // Build a fixed Jan..Dec axis and fill missing months with zeros.
                var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                var monthlyVals = new Array(12).fill(0);
                for (var i = 0; i < labels.length; i++) {
                    try {
                        var dt = new Date('1 ' + labels[i]);
                        if (!isNaN(dt.getTime())) {
                            var mi = dt.getMonth();
                            monthlyVals[mi] = Number(data[i] || 0);
                        }
                    } catch (e) {}
                }

                var barPoints = [];
                var cumPoints = [];
                var running = 0;
                for (var m = 0; m < 12; m++) {
                    var val = monthlyVals[m] || 0;
                    running += val;
                    barPoints.push({ x: m, y: val });
                    cumPoints.push({ x: m, y: running });
                }

                var cfg = {
                    type: 'bar',
                    data: {
                        datasets: [
                            { type: 'bar', label: 'Sales', data: barPoints, backgroundColor: COLORS.primaryLight, borderRadius: 6, barThickness: 'flex', maxBarThickness: 40, order: 1, yAxisID: 'y' },
                            { type: 'line', label: 'Cumulative Sales Year-to-Date', data: cumPoints, borderColor: COLORS.primary, backgroundColor: 'transparent', fill: false, tension: 0.35, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: COLORS.primary, order: 2, yAxisID: 'y' }
                        ]
                    },
                    options: Object.assign({}, defaultOptions, {
                        plugins: Object.assign({}, defaultOptions.plugins, { legend: { display: true, position: 'bottom' }, tooltip: Object.assign({}, defaultOptions.plugins.tooltip, { callbacks: {
                                    title: function (items) { try { var idx = items && items[0] && items[0].raw && typeof items[0].raw.x !== 'undefined' ? Number(items[0].raw.x) : null; return (idx !== null && months[idx]) ? months[idx] + ' ' + (new Date()).getFullYear() : ''; } catch (e) { return ''; } },
                                    label: function (ctx) { try { var v = ctx.raw && typeof ctx.raw.y !== 'undefined' ? ctx.raw.y : ctx.parsed && typeof ctx.parsed.y !== 'undefined' ? ctx.parsed.y : null; if (v === null) return ''; return ctx.dataset && ctx.dataset.label ? ctx.dataset.label + ': ' + new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(v) : new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(v); } catch (e) { return ''; } }
                                } }) }),
                        scales: {
                            x: { type: 'linear', min: 0, max: 11, grid: { color: COLORS.grid }, ticks: { font: FONT, callback: function (v) { try { return months[Math.round(Number(v))] || ''; } catch (e) { return ''; } }, stepSize: 1, padding: 6, autoSkip: false, maxRotation: 0, minRotation: 0 }, offset: false },
                            y: { id: 'y', position: 'left', beginAtZero: true, grid: { color: COLORS.grid, drawBorder: true }, ticks: { display: true, font: FONT, padding: 6, callback: function (v) { try { return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(v); } catch (e) { return '₱' + Number(v).toLocaleString(); } } } }
                        },
                        interaction: { mode: 'index', intersect: false },
                        parsing: false
                    })
                };

                try { el.style.height = '400px'; } catch (e) {}
                // Ensure no visual crop is applied so chart points remain visible
                try {
                    var p = el.parentElement || el.parentNode;
                    if (p && p.style) p.style.overflow = '';
                    el.style.marginLeft = '';
                    try { el.style.width = ''; } catch (e) {}
                } catch (e) { dlog('clear crop failed', e); }

                var chart = new Chart(el, cfg);
                try { chart._isTimeMonths = true; chart._monthLabels = months; } catch (e) {}
                return chart;
            }

            // fallback: category-based chart
            return new Chart(el, {
                type: 'line',
                data: { labels: labels, datasets: [{ label: 'Revenue (₱)', data: data, borderColor: COLORS.primary, backgroundColor: COLORS.primaryLight, fill: true, tension: 0.35, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#fff', pointBorderColor: COLORS.primary }] },
                options: Object.assign({}, defaultOptions, { plugins: Object.assign({}, defaultOptions.plugins, { legend: { display: false } }), scales: { x: { offset: true, grid: { color: COLORS.grid }, ticks: { font: FONT } }, y: { beginAtZero: true, grid: { color: COLORS.grid }, ticks: { font: FONT, callback: function (v) { try { return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(v); } catch (e) { return '₱' + Number(v).toLocaleString(); } } } } } })
            });
        } catch (e) { dlog('initSalesChart error', e); return null; }
    }

    // Orders chart
    function initOrdersChart(canvasId, labels, data) {
        var el = document.getElementById(canvasId);
        if (!el) return null;
        try {
            ilog('Initializing orders chart', canvasId);
            return new Chart(el, { type: 'bar', data: { labels: labels, datasets: [{ label: 'Orders', data: data, backgroundColor: COLORS.palette.slice(0, data.length), borderRadius: 4, maxBarThickness: 48 }] }, options: Object.assign({}, defaultOptions, { plugins: Object.assign({}, defaultOptions.plugins, { legend: { display: false } }), scales: { x: { grid: { display: false }, ticks: { font: FONT } }, y: { beginAtZero: true, grid: { color: COLORS.grid }, ticks: { font: FONT, stepSize: 1 } } } }) });
        } catch (e) { dlog('initOrdersChart error', e); return null; }
    }

    // Category doughnut with defensive sizing and retries
    function initCategoryChart(canvasId, labels, data) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        ilog('initCategoryChart called for', canvasId);

        var total = 0; try { total = Array.isArray(data) ? data.reduce(function (a, b) { return a + (Number(b) || 0); }, 0) : 0; } catch (e) { total = 0; }

        // Cleanup stray overlays
        try { var old = document.querySelectorAll && document.querySelectorAll('.chart-empty-overlay'); if (old && old.length) Array.prototype.forEach.call(old, function (x) { x.remove(); }); } catch (e) {}

        var fallback = false;
        var dataset = data;
        var datasetColors = COLORS.palette.slice(0, Array.isArray(data) ? data.length : 0);
        if (!Array.isArray(labels) || !Array.isArray(data) || labels.length === 0 || total <= 0) {
            fallback = true; labels = ['No data']; dataset = [1]; datasetColors = ['#E5E7EB'];
        }

        try { canvas.style.display = 'block'; canvas.style.visibility = 'visible'; } catch (e) {}

        var chart = null;
        try {
            chart = new Chart(canvas, {
                type: 'doughnut',
                data: { labels: labels, datasets: [{ data: dataset, backgroundColor: datasetColors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
                options: Object.assign({}, defaultOptions, { cutout: '65%', plugins: Object.assign({}, defaultOptions.plugins, { legend: { position: 'bottom', labels: { font: FONT, padding: 12, usePointStyle: true }, display: !fallback }, tooltip: Object.assign({}, defaultOptions.plugins.tooltip, { enabled: !fallback }) }) })
            });

            // Size canvas to parent and account for DPR
            try {
                var parent = canvas.parentElement || canvas.parentNode;
                var pW = parent && parent.clientWidth ? parent.clientWidth : canvas.clientWidth || 300;
                var pH = parent && parent.clientHeight ? parent.clientHeight : 280;
                var desiredH = (pH && pH >= 120) ? Math.max(200, pH - 20) : 280;
                try { canvas.style.width = '100%'; canvas.style.height = desiredH + 'px'; } catch (e) {}
                var dpr = window.devicePixelRatio || 1;
                try { canvas.width = Math.floor(pW * dpr); canvas.height = Math.floor(desiredH * dpr); var ctx = canvas.getContext && canvas.getContext('2d'); if (ctx && typeof ctx.setTransform === 'function') ctx.setTransform(dpr, 0, 0, dpr, 0, 0); } catch (e) {}
            } catch (e) { dlog('category sizing error', e); }

            // immediate and delayed renders
            try { chart.resize(); chart.update(); } catch (e) {}
            setTimeout(function () { try { chart.resize(); chart.update(); } catch (e) {} }, 140);
            setTimeout(function () { try { chart.resize(); chart.update(); } catch (e) {} }, 520);

            // fonts ready
            try { if (document && document.fonts && document.fonts.ready && typeof document.fonts.ready.then === 'function') { document.fonts.ready.then(function () { try { chart.resize(); chart.update(); } catch (e) {} }); } } catch (e) {}

            // ResizeObserver for parent
            try { if (window.ResizeObserver && parent) { var ro = new ResizeObserver(function () { try { chart.resize(); chart.update(); } catch (e) {} }); ro.observe(parent); chart._resizeObserver = ro; } } catch (e) {}

            // store
            try { window._categoryCharts = window._categoryCharts || {}; window._categoryCharts[canvasId] = chart; } catch (e) {}

            // health check retries
            try {
                (function (c, lbls, dset) {
                    var tries = 0;
                    function health() {
                        tries++;
                        try {
                            var cw = c && c.canvas && c.canvas.clientWidth ? c.canvas.clientWidth : 0;
                            var ch = c && c.canvas && c.canvas.clientHeight ? c.canvas.clientHeight : 0;
                            if (cw < 80 || ch < 80) {
                                dlog('category health-trigger resize attempt', tries, cw, ch);
                                try { c.resize(); c.update(); } catch (e) {}
                                if (tries < 4) setTimeout(health, 220 * tries);
                                else {
                                    // last resort: try to re-init once
                                    try { ilog('category reinit fallback'); window.initCategoryChart && window.initCategoryChart(canvasId, lbls, dset); } catch (e) {}
                                }
                            }
                        } catch (e) { dlog('category health error', e); }
                    }
                    setTimeout(health, 240);
                })(chart, labels, dataset);
            } catch (e) { dlog('health wrapper failed', e); }

        } catch (err) { dlog('initCategoryChart error', err); chart = null; }

        try { var parentNode = canvas.parentElement || canvas.parentNode; if (parentNode) { var ex = parentNode.querySelector('.chart-empty-overlay'); if (ex) ex.remove(); } try { chart && chart.canvas && (chart.canvas.style.zIndex = ''); } catch (e) {} } catch (e) {}

        return chart;
    }

    // If monthly labels are provided (e.g. 'Feb 2026'), ensure the current month
    // is present as the last label (with 0 value) so March 1 will show next to Feb.
    function ensureCurrentMonthPresent(labels, data) {
        try {
            if (!Array.isArray(labels) || !Array.isArray(data) || labels.length === 0) return;
            // Heuristic: monthly labels contain a 4-digit year (e.g. 'Feb 2026')
            var last = String(labels[labels.length - 1] || '');
            if (!/\d{4}/.test(last)) return; // not monthly-style labels
            var now = new Date();
            var thisMonthFirst = new Date(now.getFullYear(), now.getMonth(), 1);
            // Parse last label into a Date by prefixing day 1
            var parsed = new Date('1 ' + last);
            if (isNaN(parsed.getTime())) return;
            // If last label is already this month, nothing to do
            if (parsed.getFullYear() === thisMonthFirst.getFullYear() && parsed.getMonth() === thisMonthFirst.getMonth()) return;
            // If the current month is later than the last label, append it with 0 value
            if (thisMonthFirst > parsed) {
                labels.push(thisMonthFirst.toLocaleString('en-US', { month: 'short', year: 'numeric' }));
                data.push(0);
                dlog('Appended current month label for sales chart', labels[labels.length - 1]);
            }
        } catch (e) { dlog('ensureCurrentMonthPresent error', e); }
    }

    function initStatusChart(canvasId, labels, data) {
        var el = document.getElementById(canvasId);
        if (!el) return null;
        try { ilog('initStatusChart', canvasId); return new Chart(el, { type: 'bar', data: { labels: labels, datasets: [{ data: data, backgroundColor: [COLORS.palette[4], COLORS.palette[3], COLORS.accent, COLORS.primary, '#9E9E9E'], borderRadius: 4, maxBarThickness: 28 }] }, options: Object.assign({}, defaultOptions, { indexAxis: 'y', plugins: Object.assign({}, defaultOptions.plugins, { legend: { display: false } }), scales: { x: { beginAtZero: true, grid: { color: COLORS.grid }, ticks: { font: FONT, stepSize: 1 } }, y: { grid: { display: false }, ticks: { font: FONT } } } }) }); } catch (e) { dlog('initStatusChart error', e); return null; }

    }

    window.initSalesChart = initSalesChart;
    window.initOrdersChart = initOrdersChart;
    window.initCategoryChart = initCategoryChart;
    window.initStatusChart = initStatusChart;
    window.CHART_COLORS = COLORS;

    // Boot with wait-for-Chart and gentle retries
    function bootDashboardCharts() {
        try {
            if (!window.DASHBOARD_CHARTS) return;
            if (typeof window.Chart === 'undefined') { dlog('boot aborted: Chart undefined'); return; }
            ilog('Booting dashboard charts');
            var payload = window.DASHBOARD_CHARTS || {};

            // Sales
            if (payload.sales && payload.sales.id) {
                var sp = payload.sales;
                var labels = [];
                var data = [];
                if (sp.monthly && Array.isArray(sp.monthly.labels) && Array.isArray(sp.monthly.data)) { labels = sp.monthly.labels; data = sp.monthly.data; }
                else if (Array.isArray(sp.labels) && Array.isArray(sp.data)) { labels = sp.labels; data = sp.data; }
                try { window._salesChart = initSalesChart(sp.id, labels, data); } catch (e) { dlog('sales init error', e); }
                if (sp.daily && Array.isArray(sp.daily.labels) && Array.isArray(sp.daily.data)) { window._salesDailyLabels = sp.daily.labels.slice(); window._salesDailyData = sp.daily.data.slice(); window._salesDailyRawDates = Array.isArray(sp.daily.rawDates) ? sp.daily.rawDates.slice() : null; }
            }

            // Category: try a few silent retries if layout isn't ready
            if (payload.category && payload.category.id && Array.isArray(payload.category.labels) && Array.isArray(payload.category.data)) {
                (function (p) {
                    var attempts = 0;
                    function attempt() {
                        attempts++;
                        try {
                            var c = initCategoryChart(p.id, p.labels, p.data);
                            if (c) { ilog('category chart initialized on attempt', attempts); return; }
                        } catch (e) { dlog('category init attempt error', e); }
                        if (attempts < 6) { setTimeout(attempt, 200 * attempts); }
                        else ilog('category chart failed after attempts');
                    }
                    attempt();
                })(payload.category);
            }

            // Orders
            if (payload.orders && payload.orders.id && Array.isArray(payload.orders.labels) && Array.isArray(payload.orders.data)) {
                try { initOrdersChart(payload.orders.id, payload.orders.labels, payload.orders.data); } catch (e) { dlog('orders init error', e); }
            }

            // Status
            if (payload.status && payload.status.id && Array.isArray(payload.status.labels) && Array.isArray(payload.status.data)) {
                try { initStatusChart(payload.status.id, payload.status.labels, payload.status.data); } catch (e) { dlog('status init error', e); }
            }
        } catch (e) { dlog('bootDashboardCharts error', e); }
    }

    window.updateSalesChart = function (labels, data) {
        try {
            // Ensure monthly charts include the current month label (e.g., on Mar 1)
            try { ensureCurrentMonthPresent(labels, data); } catch (e) {}
            // If existing chart was created with time scale, update its dataset with x/y points
            if (window._salesChart && window._salesChart._isTimeMonths) {
                try {
                    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    var monthlyVals = new Array(12).fill(0);
                    for (var i = 0; i < labels.length; i++) {
                        try {
                            var dt = new Date('1 ' + labels[i]);
                            if (!isNaN(dt.getTime())) {
                                monthlyVals[dt.getMonth()] = Number(data[i] || 0);
                            }
                        } catch (e) {}
                    }
                    var barPts = [];
                    var cumPts = [];
                    var running = 0;
                    for (var m = 0; m < 12; m++) {
                        var val = monthlyVals[m] || 0;
                        running += val;
                        barPts.push({ x: m, y: val });
                        cumPts.push({ x: m, y: running });
                    }
                    if (window._salesChart.data && window._salesChart.data.datasets) {
                        if (window._salesChart.data.datasets[0]) window._salesChart.data.datasets[0].data = barPts;
                        if (window._salesChart.data.datasets[1]) window._salesChart.data.datasets[1].data = cumPts;
                        try { window._salesChart.update(); } catch (e) { dlog('updateSalesChart update error', e); }
                        return window._salesChart;
                    }
                } catch (e) { dlog('updateSalesChart time-update error', e); }
            }
            if (window._salesChart) {
                try { window._salesChart.data.labels = labels; window._salesChart.data.datasets[0].data = data; window._salesChart.update(); return window._salesChart; } catch (e) { dlog('updateSalesChart fallback update error', e); }
            }
            window._salesChart = initSalesChart('salesChart', labels, data);
            return window._salesChart;
        } catch (e) { dlog('updateSalesChart error', e); return null; }
    };

    function waitForChartAndBoot(maxAttempts, intervalMs) {
        maxAttempts = typeof maxAttempts === 'number' ? maxAttempts : 40;
        intervalMs = typeof intervalMs === 'number' ? intervalMs : 200;
        var tries = 0;
        function tick() {
            tries++;
            if (typeof window.Chart !== 'undefined') {
                if (!window._chartsBooted) { try { bootDashboardCharts(); window._chartsBooted = true; ilog('charts booted'); } catch (e) { dlog('boot error', e); } }
                return;
            }
            if (tries < maxAttempts) setTimeout(tick, intervalMs);
            else dlog('waitForChartAndBoot timed out');
        }
        tick();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { waitForChartAndBoot(40, 200); });
    else waitForChartAndBoot(40, 200);
    window.addEventListener('load', function () { waitForChartAndBoot(10, 200); });

})();
