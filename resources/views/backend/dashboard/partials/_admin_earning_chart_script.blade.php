<script>
    "use strict";

    let feeChart;
    const feeChartEl = document.querySelector("#fee-earnings-chart");

    function renderFeeChart(dataSeries, categories) {
        if (!feeChartEl) return;
        if (feeChart) feeChart.destroy();

        feeChart = new ApexCharts(feeChartEl, {
            chart: {
                type: 'bar',
                height: 224,
                parentHeightOffset: 0,
                toolbar: { show: false },
                animations: { enabled: false },
                foreColor: '#64748b',
                fontFamily: 'Inter, sans-serif'
            },
            series: dataSeries,
            xaxis: {
                type: 'datetime',
                categories: categories,
                labels: {
                    format: 'dd MMM',
                    rotate: 0,
                    hideOverlappingLabels: true,
                    style: {
                        colors: '#64748b',
                        fontWeight: 400
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            tooltip: {
                theme: 'light',
                x: { format: 'dd MMM yyyy' },
                y: {
                    formatter: val => '{{ siteCurrency("symbol") }}' + Number(val || 0).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                }
            },
            yaxis: {
                labels: {
                    formatter: val => Number(val || 0).toLocaleString(undefined, {
                        maximumFractionDigits: 0
                    }),
                    style: {
                        colors: '#64748b',
                        fontWeight: 400
                    }
                }
            },
            grid: {
                borderColor: '#e5edf7',
                strokeDashArray: 5,
                padding: {
                    top: 2,
                    right: 8,
                    bottom: 0,
                    left: 0
                }
            },
            colors: ['#4f46e5'],
            dataLabels: { enabled: false },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.18,
                    opacityFrom: 0.92,
                    opacityTo: 0.58,
                    stops: [0, 100]
                }
            },
            stroke: {
                show: false,
                width: 0
            },
            plotOptions: {
                bar: {
                    columnWidth: '32%',
                    borderRadius: 8,
                    borderRadiusApplication: 'end',
                }
            },
            states: {
                hover: {
                    filter: {
                        type: 'lighten',
                        value: 0.04
                    }
                }
            }
        });

        feeChart.render();
    }

    function fetchFeeChart(start, end) {
        if (!feeChartEl) return;

        $.ajax({
            url: "{{ route('admin.dashboard') }}",
            data: {
                start_date: start,
                end_date: end,
                fee_chart: true
            },
            success: function (res) {
                renderFeeChart(res.series, res.categories);
            }
        });
    }

    $(function () {
        if (!feeChartEl) return;

        const start = moment().subtract(6, 'days');
        const end = moment();

        function updateDisplay(start, end) {

            const sameYear = start.year() === end.year();
            const startFormat = 'MMM D, YYYY';
            const endFormat = sameYear ? 'MMM D' : 'MMM D, YYYY';
            
            $('#report-earning-range span').html(start.format(startFormat) + ' - ' + end.format(endFormat));
            $('#fee-hidden-daterange').val(start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            fetchFeeChart(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        }

        $('#report-earning-range').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                '{{ __("Today") }}': [moment(), moment()],
	            '{{ __("Last 7 Days") }}': [moment().subtract(6, 'days'), moment()],
                '{{ __("Last 30 Days") }}': [moment().subtract(29, 'days'), moment()],
                '{{ __("This Month") }}': [moment().startOf('month'), moment().endOf('month')],
                '{{ __("Last Month") }}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: 'YYYY-MM-DD'
            }
        }, updateDisplay);
        updateDisplay(start, end);
    });
</script>
