<script>
    "use strict";

    $(function () {
        const chartEl = document.querySelector("#dashboard-trx-chart");
        if (!chartEl) return;

        let chart;
        let fullSeries = {};
        let categories = [];
        const colorMap = {
            deposit: '#10b981',
            withdraw: '#fb7185',
            payment: '#2563eb',
            reward: '#f59e0b'
        };

        const $range = $('#reportrange span');
        const $input = $('#hidden-daterange');

	    
        function setDateDisplay(start, end) {
            const sameYear = start.year() === end.year();
            const startFormat = 'MMM D, YYYY';
            const endFormat = sameYear ? 'MMM D' : 'MMM D, YYYY';

            $range.text(start.format(startFormat) + ' - ' + end.format(endFormat));
            $input.val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        }


        // Initial date
        let start = moment().subtract(14, 'days');
        let end = moment();

        // Init date range picker
        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()]
            }
        }, function (startPick, endPick) {
            start = startPick;
            end = endPick;
            setDateDisplay(start, end);
            fetchChartData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });

        // Initial render
        setDateDisplay(start, end);
        fetchChartData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));

        // Fetch chart data by date range
        function fetchChartData(startDate, endDate) {
            $.ajax({
                url: "{{ route('admin.dashboard') }}",
                data: {
                    start_date: startDate,
                    end_date: endDate,
	                trx_chart: true
                },
                success: function (res) {
                    if (!res.series) return;
                    
                    

                    fullSeries = res.series.reduce((acc, item) => {
                        acc[item.name.toLowerCase()] = item;
                        return acc;
                    }, {});

                   

                    categories = res.dates.map(d => new Date(d).toISOString());

                    const seriesArray = Object.values(fullSeries);
                    const colorArray = Object.keys(fullSeries).map(type => colorMap[type] || '#888888');


                    seriesArray.forEach(({ name, total }) => {
                        const key = name?.toLowerCase?.();
                        if (!key || typeof total === 'undefined') return;

                        const el = document.getElementById(`total-${key}`);
                        if (el) {
                            el.textContent = Number(total || 0).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    });


                    if (!chart) {
                        chart = new ApexCharts(chartEl, {
                            chart: {
                                type: 'area',
                                height: 300,
                                toolbar: { show: false },
                                foreColor: '#64748b',
                                fontFamily: 'Inter, sans-serif'
                            },
                            series: seriesArray,
                            colors: colorArray,
                            xaxis: {
                                type: 'datetime',
                                categories: categories,
                                labels: {
                                    format: 'dd MMM',
                                    style: {
                                        colors: '#64748b'
                                    }
                                },
                                axisBorder: { show: false },
                                axisTicks: { show: false }
                            },
                            yaxis: {
                                labels: {
                                    formatter: (value) => '{{ siteCurrency("symbol") }}' + Number(value || 0).toLocaleString(undefined, {
                                        maximumFractionDigits: 0
                                    })
                                }
                            },
                            stroke: { curve: 'smooth', width: 3 },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.28,
                                    opacityTo: 0.02,
                                    stops: [0, 88, 100]
                                }
                            },
                            grid: {
                                borderColor: '#e2e8f0',
                                strokeDashArray: 6,
                                padding: {
                                    left: 10,
                                    right: 10
                                }
                            },
                            dataLabels: { enabled: false },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                x: { format: 'dd/MM/yyyy' },
                                y: {
                                    formatter: (value) => '{{ siteCurrency("symbol") }}' + Number(value || 0).toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    })
                                }
                            },
                            legend: { show: false }
                        });
                        chart.render();
                    } else {
                        chart.updateOptions({
                            series: seriesArray,
                            xaxis: { categories },
                            colors: colorArray
                        });
                    }

                    chart.initialSeries = fullSeries;
                }
            });
        }

        // Filter stat-card click handler
        $(document).on('click', '.stat-filter', function () {
            const type = $(this).data('type');
            const isActive = $(this).hasClass('active');

            $('.stat-filter').removeClass('active');

            if (isActive) {
                chart.updateOptions({
                    series: Object.values(chart.initialSeries),
                    colors: Object.keys(chart.initialSeries).map(t => colorMap[t] || '#888')
                });
            } else {
                $(this).addClass('active');
                if (chart.initialSeries[type]) {
                    chart.updateOptions({
                        series: [chart.initialSeries[type]],
                        colors: [colorMap[type] || '#888']
                    });
                }
            }
        });
    });
</script>
