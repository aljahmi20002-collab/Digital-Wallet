<script>
    "use strict";

    $(function () {
        const el = document.getElementById('p2pActivityChart');
        if (!el || typeof Chart === 'undefined') {
            return;
        }

        const styles = getComputedStyle(document.documentElement);
        const cssVar = (name) => styles.getPropertyValue(name).trim();
        const rgbaVar = (name, alpha) => `rgba(${cssVar(name)}, ${alpha})`;
        const chartTextSize = (name) => Number.parseFloat(cssVar(name)) || undefined;

        const data = @json($activityChart ?? ['labels' => [], 'orders' => [], 'disputes' => []]);

        if (window.p2pActivityChartInstance) {
            window.p2pActivityChartInstance.destroy();
        }

        window.p2pActivityChartInstance = new Chart(el, {
            type: 'line',
            data: {
                labels: data.labels || [],
                datasets: [
                    {
                        label: @json(__('Orders')),
                        data: data.orders || [],
                        borderColor: rgbaVar('--color-primary-rgb', '0.95'),
                        backgroundColor: rgbaVar('--color-primary-rgb', '0.08'),
                        tension: 0.35,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        borderWidth: 2,
                    },
                    {
                        label: @json(__('Disputes')),
                        data: data.disputes || [],
                        borderColor: rgbaVar('--color-danger-rgb', '0.9'),
                        backgroundColor: rgbaVar('--color-danger-rgb', '0.06'),
                        tension: 0.35,
                        fill: true,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: rgbaVar('--color-text-rgb', '0.92'),
                        padding: 10,
                        cornerRadius: 6,
                        titleFont: {size: chartTextSize('--font-xs'), weight: cssVar('--font-semibold')},
                        bodyFont: {size: chartTextSize('--font-size-px-11')},
                        displayColors: true,
                        boxPadding: 4,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: cssVar('--color-text-faint'),
                            font: {size: chartTextSize('--font-size-px-11')},
                        },
                        grid: {
                            color: rgbaVar('--color-text-faint-rgb', '0.16'),
                            drawBorder: false,
                        },
                    },
                    x: {
                        ticks: {
                            color: cssVar('--color-text-faint'),
                            font: {size: chartTextSize('--font-size-px-11')},
                        },
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                    },
                },
            },
        });
    });
</script>
