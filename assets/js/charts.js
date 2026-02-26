/**
 * SouthDev Home Depot – Charts (Chart.js v3+)
 * Professional blue/orange palette with smooth animation
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
                plugins: Object.assign({}, defaultOptions.plugins, {
                    legend: { display: false }
                }),
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
                plugins: Object.assign({}, defaultOptions.plugins, {
                    legend: { display: false }
                }),
                scales: {
                    x: { grid: { display: false }, ticks: { font: FONT } },
                    y: {
                        beginAtZero: true,
                        grid: { color: COLORS.grid },
                        ticks: { font: FONT, stepSize: 1 }
                    }
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
                datasets: [{
                    data: data,
                    backgroundColor: COLORS.palette.slice(0, data.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6
                }]
            },
            options: Object.assign({}, defaultOptions, {
                cutout: '65%',
                plugins: Object.assign({}, defaultOptions.plugins, {
                    legend: {
                        position: 'bottom',
                        labels: { font: FONT, padding: 14, usePointStyle: true, pointStyleWidth: 10 }
                    }
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
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [COLORS.warning, COLORS.info, COLORS.accent, COLORS.success, '#9E9E9E'],
                    borderRadius: 4,
                    maxBarThickness: 28
                }]
            },
            options: Object.assign({}, defaultOptions, {
                indexAxis: 'y',
                plugins: Object.assign({}, defaultOptions.plugins, {
                    legend: { display: false }
                }),
                scales: {
                    x: { beginAtZero: true, grid: { color: COLORS.grid }, ticks: { font: FONT, stepSize: 1 } },
                    y: { grid: { display: false }, ticks: { font: FONT } }
                }
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
       This prevents timing issues when charts.js is loaded via the footer.
    */
    function bootDashboardCharts() {
        try {
            if (!window.DASHBOARD_CHARTS) return;
            if (typeof window.Chart === 'undefined') return;

            var payload = window.DASHBOARD_CHARTS;
            if (payload.sales && payload.sales.id && Array.isArray(payload.sales.labels) && Array.isArray(payload.sales.data)) {
                initSalesChart(payload.sales.id, payload.sales.labels, payload.sales.data);
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
            /* no-op: avoid breaking the dashboard due to chart errors */
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootDashboardCharts);
    } else {
        bootDashboardCharts();
    }

})();
