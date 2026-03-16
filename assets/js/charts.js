/**
 * SouthDev Home Depot – Dashboard Charts  v2.0
 * Professional, polished Chart.js visualisations with gradient fills,
 * custom tooltips, smooth animations and responsive layout.
 */

(function () {
    'use strict';

    /* ── Helpers ─────────────────────────────────────────── */
    function dlog() { try { console.debug.apply(console, arguments); } catch (e) {} }

    /* ── Design Tokens ───────────────────────────────────── */
    var COLORS = {
        primary     : '#1B2A4A',
        primaryRGB  : '27,42,74',
        accent      : '#F97316',
        accentRGB   : '249,115,22',
        success     : '#2E7D32',
        info        : '#1565C0',
        warning     : '#F59E0B',
        charcoal    : '#1B2A4A',
        steel       : '#51606F',
        grid        : 'rgba(0,0,0,.05)',
        gridZero    : 'rgba(0,0,0,.10)',
        white       : '#ffffff',
        palette     : ['#1B2A4A','#F97316','#2E7D32','#1565C0','#F59E0B','#51606F','#6A1B9A','#00838F']
    };

    var FONT = {
        family : "'Inter','Segoe UI',system-ui,sans-serif",
        size   : 12,
        weight : 500
    };

    /* ── Currency formatter ──────────────────────────────── */
    var phpFmt = null;
    try { phpFmt = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }); } catch (e) {}
    function fmtPHP(v) {
        if (phpFmt) return phpFmt.format(v);
        return '₱' + Number(v).toLocaleString();
    }
    function fmtShort(v) {
        var abs = Math.abs(v);
        if (abs >= 1e6) return '₱' + (v / 1e6).toFixed(1) + 'M';
        if (abs >= 1e3) return '₱' + (v / 1e3).toFixed(0) + 'K';
        return '₱' + v;
    }

    var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    /* ── Gradient factory ────────────────────────────────── */
    function makeGradient(ctx, rgb, topAlpha, botAlpha, height) {
        var g = ctx.createLinearGradient(0, 0, 0, height || 400);
        g.addColorStop(0, 'rgba(' + rgb + ',' + topAlpha + ')');
        g.addColorStop(1, 'rgba(' + rgb + ',' + botAlpha + ')');
        return g;
    }

    /* ── Shared defaults ─────────────────────────────────── */
    var defaultPlugins = {
        legend: {
            position: 'bottom',
            labels: { font: { family: FONT.family, size: 12, weight: 600 }, padding: 16, usePointStyle: true, pointStyleWidth: 10, boxWidth: 8, boxHeight: 8 }
        },
        tooltip: {
            backgroundColor: 'rgba(44,62,80,.92)',
            titleFont: { family: FONT.family, size: 13, weight: 700 },
            bodyFont:  { family: FONT.family, size: 12, weight: 500 },
            padding: { top: 10, bottom: 10, left: 14, right: 14 },
            cornerRadius: 8,
            displayColors: true,
            boxPadding: 6,
            caretSize: 6,
            caretPadding: 8,
            usePointStyle: true
        }
    };

    /* ================================================================
       SALES CHART  — bar (monthly revenue) + line (cumulative YTD)
       ================================================================ */
    function initSalesChart(canvasId, labels, data) {
        var el = document.getElementById(canvasId);
        if (!el) return null;

        try {
            var useTimeScale = Array.isArray(labels) && labels.length > 0 && /\d{4}/.test(String(labels[0]));
            if (useTimeScale) {
                var monthlyVals = new Array(12).fill(0);
                for (var i = 0; i < labels.length; i++) {
                    try {
                        var dt = new Date('1 ' + labels[i]);
                        if (!isNaN(dt.getTime())) monthlyVals[dt.getMonth()] = Number(data[i] || 0);
                    } catch (e) {}
                }

                /* Find last month that has actual sales data */
                var lastDataMonth = -1;
                for (var mi = 11; mi >= 0; mi--) {
                    if (monthlyVals[mi] > 0) { lastDataMonth = mi; break; }
                }

                var barData = [], lineData = [];
                for (var m = 0; m < 12; m++) {
                    var val = monthlyVals[m] || 0;
                    barData.push(val);
                    /* Line follows bar tops; null for months with no data */
                    lineData.push(m <= lastDataMonth ? val : null);
                }

                /* Canvas gradient for bar fill */
                var ctx2d = el.getContext('2d');
                var barGrad = makeGradient(ctx2d, COLORS.primaryRGB, 0.22, 0.03, 400);
                var lineGrad = makeGradient(ctx2d, COLORS.primaryRGB, 0.15, 0.01, 400);

                var cfg = {
                    type: 'bar',
                    data: {
                        labels: MONTHS,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Monthly Sales',
                                data: barData,
                                backgroundColor: barGrad,
                                hoverBackgroundColor: 'rgba(' + COLORS.primaryRGB + ',.40)',
                                borderRadius: { topLeft: 5, topRight: 5 },
                                borderSkipped: 'bottom',
                                barPercentage: 0.5,
                                categoryPercentage: 0.7,
                                order: 2,
                                yAxisID: 'y'
                            },
                            {
                                type: 'line',
                                label: 'Sales Trend',
                                data: lineData,
                                borderColor: COLORS.primary,
                                backgroundColor: lineGrad,
                                fill: true,
                                spanGaps: false,
                                tension: 0.35,
                                borderWidth: 2.5,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: COLORS.white,
                                pointBorderColor: COLORS.primary,
                                pointBorderWidth: 2.5,
                                pointHoverBorderWidth: 3,
                                pointHoverBackgroundColor: COLORS.white,
                                order: 1,
                                yAxisID: 'y'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 800, easing: 'easeOutQuart' },
                        layout: { padding: { left: 4, right: 4, top: 12, bottom: 4 } },
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: defaultPlugins.legend,
                            tooltip: Object.assign({}, defaultPlugins.tooltip, {
                                callbacks: {
                                    title: function (items) {
                                        try {
                                            var label = items[0].label || '';
                                            return label + ' ' + (new Date()).getFullYear();
                                        } catch (e) { return ''; }
                                    },
                                    label: function (ctx) {
                                        try {
                                            var v = ctx.parsed.y;
                                            return '  ' + ctx.dataset.label + ':  ' + fmtPHP(v);
                                        } catch (e) { return ''; }
                                    }
                                }
                            })
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    font: { family: FONT.family, size: 11, weight: 600 },
                                    color: COLORS.steel,
                                    padding: 8, maxRotation: 0
                                }
                            },
                            y: {
                                position: 'left',
                                beginAtZero: true,
                                border: { display: false, dash: [4, 4] },
                                grid: { color: function (ctx) { return ctx.tick.value === 0 ? COLORS.gridZero : COLORS.grid; }, drawTicks: false },
                                ticks: {
                                    font: { family: FONT.family, size: 11, weight: 600 },
                                    color: COLORS.steel,
                                    padding: 12,
                                    callback: function (v) { return fmtShort(v); },
                                    maxTicksLimit: 7
                                }
                            }
                        }
                    }
                };

                try { el.style.height = '380px'; } catch (e) {}
                try {
                    var p = el.parentElement;
                    if (p) { p.style.overflow = ''; p.style.position = 'relative'; }
                } catch (e) {}

                var chart = new Chart(el, cfg);
                chart._isTimeMonths = true;
                chart._monthLabels = MONTHS;
                chart._monthlyVals = monthlyVals;
                return chart;
            }

            /* ── Fallback: generic line chart ── */
            var ctx2dFB = el.getContext('2d');
            var fbGrad = makeGradient(ctx2dFB, COLORS.primaryRGB, 0.20, 0.01, 350);
            return new Chart(el, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (₱)', data: data,
                        borderColor: COLORS.primary, backgroundColor: fbGrad, fill: true,
                        tension: 0.4, borderWidth: 2.5,
                        pointRadius: 4, pointHoverRadius: 6,
                        pointBackgroundColor: COLORS.white, pointBorderColor: COLORS.primary, pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    animation: { duration: 800, easing: 'easeOutQuart' },
                    layout: { padding: { left: 8, right: 16, top: 12, bottom: 4 } },
                    plugins: { legend: { display: false }, tooltip: defaultPlugins.tooltip },
                    scales: {
                        x: { offset: true, grid: { display: false }, border: { display: false }, ticks: { font: FONT, color: COLORS.steel } },
                        y: { beginAtZero: true, border: { display: false }, grid: { color: COLORS.grid, drawTicks: false }, ticks: { font: FONT, color: COLORS.steel, padding: 12, callback: function (v) { return fmtShort(v); } } }
                    }
                }
            });
        } catch (e) { dlog('initSalesChart error', e); return null; }
    }

    /* ================================================================
       ORDERS CHART  — clean vertical bar with rounded tops
       ================================================================ */
    function initOrdersChart(canvasId, labels, data) {
        var el = document.getElementById(canvasId);
        if (!el) return null;
        try {
            return new Chart(el, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Orders', data: data,
                        backgroundColor: COLORS.palette.slice(0, data.length).map(function (c) { return c + 'CC'; }),
                        hoverBackgroundColor: COLORS.palette.slice(0, data.length),
                        borderRadius: { topLeft: 6, topRight: 6 },
                        borderSkipped: 'bottom',
                        maxBarThickness: 44
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    layout: { padding: { left: 8, right: 8, top: 8, bottom: 4 } },
                    plugins: { legend: { display: false }, tooltip: defaultPlugins.tooltip },
                    scales: {
                        x: { grid: { display: false }, border: { display: false }, ticks: { font: { family: FONT.family, size: 11, weight: 600 }, color: COLORS.steel } },
                        y: { beginAtZero: true, border: { display: false }, grid: { color: COLORS.grid, drawTicks: false }, ticks: { font: FONT, color: COLORS.steel, stepSize: 1, padding: 10 } }
                    }
                }
            });
        } catch (e) { dlog('initOrdersChart error', e); return null; }
    }

    /* ================================================================
       CATEGORY DOUGHNUT  — with centre label + clean legend
       ================================================================ */
    function initCategoryChart(canvasId, labels, data) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        var total = 0;
        try { total = Array.isArray(data) ? data.reduce(function (a, b) { return a + (Number(b) || 0); }, 0) : 0; } catch (e) {}

        /* Clean up any stray overlays */
        try { var old = document.querySelectorAll('.chart-empty-overlay'); if (old) old.forEach(function (x) { x.remove(); }); } catch (e) {}

        var fallback = false, dataset = data;
        var datasetColors = COLORS.palette.slice(0, Array.isArray(data) ? data.length : 0);
        if (!Array.isArray(labels) || !Array.isArray(data) || labels.length === 0 || total <= 0) {
            fallback = true; labels = ['No data']; dataset = [1]; datasetColors = ['#E5E7EB'];
        }

        try { canvas.style.display = 'block'; canvas.style.visibility = 'visible'; } catch (e) {}

        /* Centre text plugin */
        var centreTextPlugin = {
            id: 'centreText',
            afterDraw: function (chart) {
                if (fallback) return;
                var ctx = chart.ctx, w = chart.width, h = chart.chartArea ? (chart.chartArea.top + chart.chartArea.bottom) / 2 : h / 2;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.fillStyle = COLORS.charcoal;
                ctx.font = '700 22px ' + FONT.family;
                ctx.fillText(fmtShort(total), w / 2, h - 6);
                ctx.font = '500 11px ' + FONT.family;
                ctx.fillStyle = COLORS.steel;
                ctx.fillText('Total Revenue', w / 2, h + 14);
                ctx.restore();
            }
        };

        var chart = null;
        try {
            chart = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataset,
                        backgroundColor: datasetColors,
                        borderWidth: 3,
                        borderColor: COLORS.white,
                        hoverOffset: 8,
                        hoverBorderWidth: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '68%',
                    animation: { duration: 900, easing: 'easeOutQuart', animateRotate: true },
                    layout: { padding: 4 },
                    plugins: {
                        legend: {
                            position: 'bottom', display: !fallback,
                            labels: { font: { family: FONT.family, size: 11, weight: 600 }, padding: 14, usePointStyle: true, pointStyleWidth: 9, boxWidth: 8, boxHeight: 8 }
                        },
                        tooltip: Object.assign({}, defaultPlugins.tooltip, {
                            enabled: !fallback,
                            callbacks: {
                                label: function (ctx) {
                                    try {
                                        var pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                        return '  ' + ctx.label + ':  ' + fmtPHP(ctx.raw) + '  (' + pct + '%)';
                                    } catch (e) { return ''; }
                                }
                            }
                        })
                    }
                },
                plugins: [centreTextPlugin]
            });

            /* Sizing helpers */
            try {
                var parent = canvas.parentElement;
                var pH = parent && parent.clientHeight >= 120 ? Math.max(200, parent.clientHeight - 20) : 280;
                canvas.style.width = '100%';
                canvas.style.height = pH + 'px';
            } catch (e) {}

            /* Delayed layout fixes */
            setTimeout(function () { try { chart.resize(); } catch (e) {} }, 150);
            setTimeout(function () { try { chart.resize(); } catch (e) {} }, 500);
            try { if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { try { chart.resize(); } catch (e) {} }); } catch (e) {}
            try { if (window.ResizeObserver && parent) { var ro = new ResizeObserver(function () { try { chart.resize(); } catch (e) {} }); ro.observe(parent); chart._resizeObserver = ro; } } catch (e) {}

            window._categoryCharts = window._categoryCharts || {};
            window._categoryCharts[canvasId] = chart;
        } catch (err) { dlog('initCategoryChart error', err); chart = null; }

        return chart;
    }

    /* ================================================================
       STATUS CHART  — horizontal bar, clean
       ================================================================ */
    function initStatusChart(canvasId, labels, data) {
        var el = document.getElementById(canvasId);
        if (!el) return null;
        try {
            var statusColors = [COLORS.warning, COLORS.info, COLORS.accent, COLORS.primary, '#9E9E9E'];
            return new Chart(el, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: statusColors.map(function (c) { return c + 'CC'; }),
                        hoverBackgroundColor: statusColors,
                        borderRadius: 4,
                        maxBarThickness: 26
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    layout: { padding: { left: 4, right: 12, top: 4, bottom: 4 } },
                    plugins: { legend: { display: false }, tooltip: defaultPlugins.tooltip },
                    scales: {
                        x: { beginAtZero: true, border: { display: false }, grid: { color: COLORS.grid, drawTicks: false }, ticks: { font: FONT, color: COLORS.steel, stepSize: 1, padding: 6 } },
                        y: { grid: { display: false }, border: { display: false }, ticks: { font: { family: FONT.family, size: 12, weight: 600 }, color: COLORS.charcoal, padding: 6 } }
                    }
                }
            });
        } catch (e) { dlog('initStatusChart error', e); return null; }
    }

    /* ── Ensure current month present ────────────────────── */
    function ensureCurrentMonthPresent(labels, data) {
        try {
            if (!Array.isArray(labels) || !Array.isArray(data) || labels.length === 0) return;
            var last = String(labels[labels.length - 1] || '');
            if (!/\d{4}/.test(last)) return;
            var now = new Date();
            var thisMonthFirst = new Date(now.getFullYear(), now.getMonth(), 1);
            var parsed = new Date('1 ' + last);
            if (isNaN(parsed.getTime())) return;
            if (parsed.getFullYear() === thisMonthFirst.getFullYear() && parsed.getMonth() === thisMonthFirst.getMonth()) return;
            if (thisMonthFirst > parsed) {
                labels.push(thisMonthFirst.toLocaleString('en-US', { month: 'short', year: 'numeric' }));
                data.push(0);
            }
        } catch (e) {}
    }

    /* ── Public API ──────────────────────────────────────── */
    window.initSalesChart      = initSalesChart;
    window.initDailySalesChart = initDailySalesChart;
    window.initOrdersChart     = initOrdersChart;
    window.initCategoryChart   = initCategoryChart;
    window.initStatusChart     = initStatusChart;
    window.CHART_COLORS        = COLORS;

    /* ── Boot ─────────────────────────────────────────────── */
    function bootDashboardCharts() {
        try {
            if (!window.DASHBOARD_CHARTS || typeof window.Chart === 'undefined') return;
            var payload = window.DASHBOARD_CHARTS;

            /* Sales — store payload for switchSalesView; only init if no toggle buttons present */
            if (payload.sales && payload.sales.id) {
                var sp = payload.sales;
                /* Check if toggle buttons exist — if they do, setView() on DOMContentLoaded will init the chart */
                var hasToggle = document.getElementById('sales-view-monthly') || document.getElementById('sales-view-daily');
                if (!hasToggle) {
                    var labels = [], data = [];
                    if (sp.monthly && Array.isArray(sp.monthly.labels)) { labels = sp.monthly.labels; data = sp.monthly.data; }
                    else if (Array.isArray(sp.labels)) { labels = sp.labels; data = sp.data; }
                    try { window._salesChart = initSalesChart(sp.id, labels, data); } catch (e) { dlog('sales init error', e); }
                }
            }

            /* Category */
            if (payload.category && payload.category.id && Array.isArray(payload.category.labels)) {
                (function (p) {
                    var attempts = 0;
                    (function tryInit() {
                        attempts++;
                        var c = initCategoryChart(p.id, p.labels, p.data);
                        if (!c && attempts < 5) setTimeout(tryInit, 200 * attempts);
                    })();
                })(payload.category);
            }

            /* Orders */
            if (payload.orders && payload.orders.id && Array.isArray(payload.orders.labels)) {
                try { initOrdersChart(payload.orders.id, payload.orders.labels, payload.orders.data); } catch (e) {}
            }

            /* Status */
            if (payload.status && payload.status.id && Array.isArray(payload.status.labels)) {
                try { initStatusChart(payload.status.id, payload.status.labels, payload.status.data); } catch (e) {}
            }
        } catch (e) { dlog('bootDashboardCharts error', e); }
    }

    /* ================================================================
       DAILY SALES CHART — bar (daily revenue) + area trend (last 30 days)
       ================================================================ */
    function initDailySalesChart(canvasId, labels, data) {
        var el = document.getElementById(canvasId);
        if (!el) return null;

        try {
            /* Remove future zero-trailing days after last day with data */
            var lastIdx = -1;
            for (var li = data.length - 1; li >= 0; li--) {
                if (Number(data[li] || 0) > 0) { lastIdx = li; break; }
            }

            var barData = data.slice();
            var lineData = [];
            for (var di = 0; di < data.length; di++) {
                lineData.push(di <= lastIdx ? Number(data[di] || 0) : null);
            }

            /* Show only every Nth label to avoid overcrowding */
            var tickInterval = labels.length > 20 ? 5 : (labels.length > 10 ? 3 : 1);

            var ctx2d = el.getContext('2d');
            var barGrad  = makeGradient(ctx2d, COLORS.accentRGB, 0.35, 0.05, 400);
            var lineGrad = makeGradient(ctx2d, COLORS.accentRGB, 0.18, 0.01, 400);

            var cfg = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Daily Sales',
                            data: barData,
                            backgroundColor: barGrad,
                            hoverBackgroundColor: 'rgba(' + COLORS.accentRGB + ',.55)',
                            borderRadius: { topLeft: 3, topRight: 3 },
                            borderSkipped: 'bottom',
                            barPercentage: 0.65,
                            categoryPercentage: 0.85,
                            order: 2,
                            yAxisID: 'y'
                        },
                        {
                            type: 'line',
                            label: 'Daily Trend',
                            data: lineData,
                            borderColor: COLORS.accent,
                            backgroundColor: lineGrad,
                            fill: true,
                            spanGaps: false,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: COLORS.white,
                            pointBorderColor: COLORS.accent,
                            pointBorderWidth: 2,
                            pointHoverBorderWidth: 2.5,
                            pointHoverBackgroundColor: COLORS.white,
                            order: 1,
                            yAxisID: 'y'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 700, easing: 'easeOutQuart' },
                    layout: { padding: { left: 4, right: 4, top: 12, bottom: 4 } },
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: defaultPlugins.legend,
                        tooltip: Object.assign({}, defaultPlugins.tooltip, {
                            callbacks: {
                                title: function (items) {
                                    try {
                                        var label = items[0].label || '';
                                        return label + ', ' + (new Date()).getFullYear();
                                    } catch (e) { return ''; }
                                },
                                label: function (ctx) {
                                    try {
                                        var v = ctx.parsed.y;
                                        return '  ' + ctx.dataset.label + ':  ' + fmtPHP(v);
                                    } catch (e) { return ''; }
                                }
                            }
                        })
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            ticks: {
                                font: { family: FONT.family, size: 10, weight: 600 },
                                color: COLORS.steel,
                                padding: 6,
                                maxRotation: 45,
                                minRotation: 0,
                                autoSkip: true,
                                autoSkipPadding: 12,
                                callback: function (val, idx) {
                                    /* Show every Nth label for readability */
                                    return idx % tickInterval === 0 ? this.getLabelForValue(val) : '';
                                }
                            }
                        },
                        y: {
                            position: 'left',
                            beginAtZero: true,
                            border: { display: false, dash: [4, 4] },
                            grid: { color: function (ctx) { return ctx.tick.value === 0 ? COLORS.gridZero : COLORS.grid; }, drawTicks: false },
                            ticks: {
                                font: { family: FONT.family, size: 11, weight: 600 },
                                color: COLORS.steel,
                                padding: 12,
                                callback: function (v) { return fmtShort(v); },
                                maxTicksLimit: 7
                            }
                        }
                    }
                }
            };

            try { el.style.height = '380px'; } catch (e) {}
            try {
                var p = el.parentElement;
                if (p) { p.style.overflow = ''; p.style.position = 'relative'; }
            } catch (e) {}

            var chart = new Chart(el, cfg);
            chart._isDailyChart = true;
            return chart;
        } catch (e) { dlog('initDailySalesChart error', e); return null; }
    }

    /* ── Update / switch sales chart (monthly ↔ daily) ─── */
    window.switchSalesView = function (view) {
        try {
            var payload = window.DASHBOARD_CHARTS && window.DASHBOARD_CHARTS.sales;
            if (!payload) return null;

            /* Always destroy old chart first for a clean swap */
            if (window._salesChart) {
                try { window._salesChart.destroy(); } catch (e) {}
                window._salesChart = null;
            }

            if (view === 'daily') {
                var dLabels = payload.daily ? payload.daily.labels : [];
                var dData   = payload.daily ? payload.daily.data   : [];
                if (dLabels.length > 0) {
                    window._salesChart = initDailySalesChart(payload.id, dLabels, dData);
                }
            } else {
                /* Monthly (default) */
                var mLabels = [];
                var mData   = [];
                if (payload.monthly && Array.isArray(payload.monthly.labels)) {
                    mLabels = payload.monthly.labels;
                    mData   = payload.monthly.data;
                } else if (Array.isArray(payload.labels)) {
                    mLabels = payload.labels;
                    mData   = payload.data;
                }
                window._salesChart = initSalesChart(payload.id, mLabels, mData);
            }
            return window._salesChart;
        } catch (e) { dlog('switchSalesView error', e); return null; }
    };

    /* Keep legacy updateSalesChart for any external callers */
    window.updateSalesChart = function (labels, data) {
        try {
            if (window._salesChart) { try { window._salesChart.destroy(); } catch (e) {} }
            window._salesChart = initSalesChart('salesChart', labels, data);
            return window._salesChart;
        } catch (e) { dlog('updateSalesChart error', e); return null; }
    };

    /* ── Wait for Chart.js CDN then boot ─────────────────── */
    function waitForChartAndBoot(max, ms) {
        max = max || 40; ms = ms || 200;
        var tries = 0;
        (function tick() {
            tries++;
            if (typeof window.Chart !== 'undefined') {
                if (!window._chartsBooted) { bootDashboardCharts(); window._chartsBooted = true; }
                return;
            }
            if (tries < max) setTimeout(tick, ms);
        })();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { waitForChartAndBoot(); });
    else waitForChartAndBoot();
    window.addEventListener('load', function () { waitForChartAndBoot(10, 200); });

})();
