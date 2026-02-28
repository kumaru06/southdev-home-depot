/**
 * SouthDev Home Depot – Charts (Chart.js v3+)
 * Restored to simpler behavior: no automatic canvas resizing, no midnight rollover,
 * no tick suppression. Keeps chart initialization and simple update API.
 */

(function () {
    'use strict';

    /* ===== Design Tokens ===== */
    var COLORS = {
        primary:     '#0B3D91',
        primaryLight:'rgba(11,61,145,.14)',
        accent:      '#FF6B00',
        accentLight: 'rgba(255,107,0,.16)',
        charcoal:    '#2C3E50',
        graphite:    '#253445',
        steel:       '#51606F',
        neutral:     '#F5F6FA',
        grid:        'rgba(0,0,0,.06)',
        success:     '#2E7D32',
        warning:     '#F59E0B',
        info:        '#1565C0',
        palette: [
            '#0B3D91',
            '#FF6B00',
            '#2E7D32',
            '#1565C0',
            '#F59E0B',
            '#51606F',
            '#6A1B9A',
            '#00838F'
        ]
    };

    var FONT = { family: "'Inter','Segoe UI',sans-serif", size: 12, weight: 500 };

    var defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 800, easing: 'easeOutQuart' },
        plugins: {
            legend: { labels: { font: FONT, padding: 16, usePointStyle: true } },
            tooltip: {
                backgroundColor: COLORS.charcoal,
                titleFont: { family: FONT.family, size: 13, weight: 700 },
                bodyFont: { family: FONT.family, size: 12 },
                padding: 12,
                cornerRadius: 6,
                displayColors: true
            }
        }
    };

    /* ===== Sales Line Chart ===== */
    function initSalesChart(canvasId, labels, data) {
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: data,
                    borderColor: COLORS.primary,
                    backgroundColor: COLORS.primaryLight,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: COLORS.primary,
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }]
            },
            options: Object.assign({}, defaultOptions, {
                plugins: Object.assign({}, defaultOptions.plugins, { legend: { display: false } }),
                scales: {
                    x: { grid: { color: COLORS.grid }, ticks: { font: FONT } },
                    y: {
                        beginAtZero: true,
                        grid: { color: COLORS.grid },
                        ticks: {
                            font: FONT,
                            callback: function (val) { return '₱' + val.toLocaleString(); }
                        }
                    }
                }
            })
        });
    }

    /* ===== Orders Bar Chart ===== */
    function initOrdersChart(canvasId, labels, data) {
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders',
                    data: data,
                    backgroundColor: COLORS.palette.slice(0, data.length),
                    borderRadius: 4,
                    maxBarThickness: 48
                }]
            },
            options: Object.assign({}, defaultOptions, {
                plugins: Object.assign({}, defaultOptions.plugins, { legend: { display: false } }),
                scales: {
                    x: { grid: { display: false }, ticks: { font: FONT } },
                    y: { beginAtZero: true, grid: { color: COLORS.grid }, ticks: { font: FONT, stepSize: 1 } }
                }
            })
        });
    }

    /* ===== Category Doughnut Chart ===== */
    function initCategoryChart(canvasId, labels, data) {
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: data, backgroundColor: COLORS.palette.slice(0, data.length), borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
            },
            options: Object.assign({}, defaultOptions, {
                cutout: '65%',
                plugins: Object.assign({}, defaultOptions.plugins, {
                    legend: { position: 'bottom', labels: { font: FONT, padding: 14, usePointStyle: true, pointStyleWidth: 10 } }
                })
            })
        });
    }

    /* ===== Status Horizontal Bar ===== */
    function initStatusChart(canvasId, labels, data) {
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;

        return new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ data: data, backgroundColor: [COLORS.warning, COLORS.info, COLORS.accent, COLORS.success, '#9E9E9E'], borderRadius: 4, maxBarThickness: 28 }] },
            options: Object.assign({}, defaultOptions, {
                indexAxis: 'y',
                plugins: Object.assign({}, defaultOptions.plugins, { legend: { display: false } }),
                scales: { x: { beginAtZero: true, grid: { color: COLORS.grid }, ticks: { font: FONT, stepSize: 1 } }, y: { grid: { display: false }, ticks: { font: FONT } } }
            })
        });
    }

    /* Expose */
    window.initSalesChart = initSalesChart;
    window.initOrdersChart = initOrdersChart;
    window.initCategoryChart = initCategoryChart;
    window.initStatusChart = initStatusChart;
    window.CHART_COLORS = COLORS;

    /* ===== Dashboard Bootstrapping =====
       If a page sets `window.DASHBOARD_CHARTS`, initialize charts automatically.
    */
    function bootDashboardCharts() {
        try {
            if (!window.DASHBOARD_CHARTS) return;
            if (typeof window.Chart === 'undefined') return;

            var payload = window.DASHBOARD_CHARTS;
            if (payload.sales && payload.sales.id) {
                var salesPayload = payload.sales;
                var initLabels = [];
                var initData = [];
                if (salesPayload.monthly && Array.isArray(salesPayload.monthly.labels) && Array.isArray(salesPayload.monthly.data)) {
                    initLabels = salesPayload.monthly.labels;
                    initData = salesPayload.monthly.data;
                } else if (Array.isArray(salesPayload.labels) && Array.isArray(salesPayload.data)) {
                    initLabels = salesPayload.labels;
                    initData = salesPayload.data;
                }
                window._salesChart = initSalesChart(salesPayload.id, initLabels, initData);
            }
            if (payload.category && payload.category.id && Array.isArray(payload.category.labels) && Array.isArray(payload.category.data)) {
                initCategoryChart(payload.category.id, payload.category.labels, payload.category.data);
            }
            if (payload.orders && payload.orders.id && Array.isArray(payload.orders.labels) && Array.isArray(payload.orders.data)) {
                initOrdersChart(payload.orders.id, payload.orders.labels, payload.orders.data);
            }
            if (payload.status && payload.status.id && Array.isArray(payload.status.labels) && Array.isArray(payload.status.data)) {
                initStatusChart(payload.status.id, payload.status.labels, payload.status.data);
            }
        } catch (e) {
            /* no-op */
        }
    }

    window.updateSalesChart = function (labels, data) {
        try {
            if (window._salesChart) {
                window._salesChart.data.labels = labels;
                window._salesChart.data.datasets[0].data = data;
                // If many labels (daily), force show all ticks and expand canvas so they are sequential
                try {
                    var canvas = window._salesChart.canvas;
                    var ticksOpts = window._salesChart.options && window._salesChart.options.scales && window._salesChart.options.scales.x && window._salesChart.options.scales.x.ticks;
                        if (Array.isArray(labels) && labels.length > 20) {
                            // Keep daily labels visually consistent with monthly: same font size and no rotation.
                            if (ticksOpts) {
                                ticksOpts.autoSkip = true;
                                ticksOpts.maxRotation = 0;
                                ticksOpts.minRotation = 0;
                                try { ticksOpts.font = FONT; } catch (e) {}
                                ticksOpts.align = undefined;
                                try {
                                    if (window._salesChart && window._salesChart.options && window._salesChart.options.layout) {
                                        window._salesChart.options.layout.padding = Object.assign({}, window._salesChart.options.layout.padding, { bottom: 0 });
                                    }
                                } catch (e) {}
                            }
                            if (canvas && canvas.style) {
                                // reset any canvas sizing so it fits container like monthly
                                canvas.style.width = '';
                                canvas.style.minWidth = '';
                                canvas.style.height = '';
                                try { window._salesChart.resize(); } catch (e) {}
                            }
                        } else {
                            // reset to normal for monthly view (same as above)
                            if (ticksOpts) {
                                ticksOpts.autoSkip = true;
                                ticksOpts.maxRotation = 0;
                                ticksOpts.minRotation = 0;
                                try { ticksOpts.font = FONT; } catch (e) {}
                                ticksOpts.align = undefined;
                            }
                            if (canvas && canvas.style) {
                                canvas.style.width = '';
                                canvas.style.minWidth = '';
                                canvas.style.height = '';
                            }
                        }
                } catch (e) {}
                window._salesChart.update();
                return window._salesChart;
            }
            window._salesChart = initSalesChart('salesChart', labels, data);
            return window._salesChart;
        } catch (e) {
            return null;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootDashboardCharts);
    } else {
        bootDashboardCharts();
    }

})();
