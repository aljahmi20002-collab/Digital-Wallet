<script>
    "use strict";

    const successColor = ["rgba(22, 163, 122, 0.78)", "rgba(187, 247, 208, 0.42)"];
    const failColor = ["rgba(220, 38, 38, 0.72)", "rgba(254, 202, 202, 0.42)"];

    const depositData = @json($sortedDeposits);
    const withdrawData = @json($sortedWithdrawals);
    const mobileChartMedia = window.matchMedia('(max-width: 991.98px)');

    function getMaxValue(data, successKey, failKey) {
        return data.reduce((maxVal, item) =>
            Math.max(maxVal, item[successKey] ?? 0, item[failKey] ?? 0), 0);
    }

    const depositMax = getMaxValue(depositData, 'success_total', 'fail_total');
    const withdrawMax = getMaxValue(withdrawData, 'withdraw_success_total', 'withdraw_fail_total');
    const globalMax = Math.max(depositMax, withdrawMax);

    function renderChart(elementId, categories, successData, failData, dataset, labels, yAxisMax) {
        const options = {
            series: [
                {name: labels[0], data: successData},
                {name: labels[1], data: failData}
            ],
            chart: {
                height: 200,
                type: 'area',
                toolbar: {show: false},
                sparkline: {enabled: false},
                parentHeightOffset: 0
            },
            grid: {
                show: true,
                borderColor: "#E8EDF6",
                strokeDashArray: 4,
                padding: {left: 4, right: 8, top: 2, bottom: 0}
            },
            dataLabels: {enabled: false},
            stroke: {curve: 'smooth', width: 2.2, colors: [successColor[0], failColor[0]]},
            xaxis: {
                tooltip: {enabled: false},
                categories: categories,
                crosshairs: {show: false},
                tickPlacement: 'between',
                labels: {
                    show: true,
                    rotate: 0,
                    hideOverlappingLabels: true,
                    trim: true,
                    style: {colors: "#667085", fontSize: "12px", fontWeight: 500}
                }
            },
            yaxis: {
                min: 0,
                max: yAxisMax > 0 ? Math.ceil(yAxisMax * 1.12) : 100,
                tickAmount: 5,
                forceNiceScale: true,
                labels: {
                    style: {colors: "#667085", fontSize: "12px", fontWeight: 500}
                }
            },
            tooltip: {
                theme: 'light',
                shared: true,
                intersect: false,
                x: {formatter: (value) => "Day: " + value},
                y: {
                    formatter: (val, {dataPointIndex}) => {
                        const symbol = dataset[dataPointIndex]?.symbol ?? '$';
                        return symbol + val.toFixed(2);
                    }
                }
            },
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 0.5,
                    opacityFrom: 0.34,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            colors: [successColor[0], failColor[0]],
            markers: {size: 0, strokeWidth: 0, hover: {size: 0}},
            legend: {show: false},
            responsive: [
                {
                    breakpoint: 992,
                    options: {
                        chart: {
                            height: 168
                        },
                        grid: {
                            padding: {left: 0, right: 2, top: 4, bottom: 0}
                        },
                        stroke: {
                            width: 2.35
                        },
                        xaxis: {
                            labels: {
                                style: {fontSize: "10px"}
                            }
                        },
                        yaxis: {
                            labels: {
                                show: false
                            }
                        }
                    }
                },
                {
                    breakpoint: 576,
                    options: {
                        chart: {
                            height: 150
                        },
                        stroke: {
                            width: 2.1
                        },
                        xaxis: {
                            labels: {
                                style: {fontSize: "9px"}
                            }
                        },
                        fill: {
                            gradient: {
                                opacityFrom: 0.58,
                                opacityTo: 0.12
                            }
                        }
                    }
                }
            ]
        };

        new ApexCharts(document.querySelector(elementId), options).render();
    }

    if (document.querySelector("#deposit-chart")) {
        renderChart(
            "#deposit-chart",
            depositData.map(item => item.day),
            depositData.map(item => item.success_total),
            depositData.map(item => item.fail_total),
            depositData,
            ['Success Deposits', 'Failed Deposits'],
            globalMax
        );
    }

    if (document.querySelector("#withdraw-chart")) {
        renderChart(
            "#withdraw-chart",
            withdrawData.map(item => item.day),
            withdrawData.map(item => item.withdraw_success_total),
            withdrawData.map(item => item.withdraw_fail_total),
            withdrawData,
            ['Success Withdrawals', 'Failed Withdrawals'],
            globalMax
        );
    }

    document.addEventListener("DOMContentLoaded", function() {
        let toggleBtn = document.getElementById("toggleLinksBtn");
        let hiddenLinks = document.querySelectorAll(".more-links");
        let isExpanded = false;
        const chartCards = document.querySelectorAll("[data-chart-card]");

        const updateChartToggleLabel = function(toggle, expanded) {
            if (!toggle) {
                return;
            }

            const label = toggle.querySelector("[data-chart-toggle-label]");
            const nextLabel = expanded ? toggle.dataset.expandedLabel : toggle.dataset.collapsedLabel;

            toggle.setAttribute("aria-expanded", expanded ? "true" : "false");

            if (label) {
                label.textContent = nextLabel;
            }
        };

        const syncChartCards = function() {
            chartCards.forEach(function(card) {
                const toggle = card.querySelector("[data-chart-toggle]");
                const shouldExpand = !mobileChartMedia.matches;

                card.classList.toggle("is-mobile-expanded", shouldExpand);
                updateChartToggleLabel(toggle, shouldExpand);
            });
        };

        if (toggleBtn) {
            toggleBtn.addEventListener("click", function() {
                hiddenLinks.forEach(el => el.classList.toggle("d-none"));
                isExpanded = !isExpanded;
                toggleBtn.textContent = isExpanded ? "{{ __('Load Less') }}" : "{{ __('Load More') }}";
            });
        }

        chartCards.forEach(function(card) {
            const toggle = card.querySelector("[data-chart-toggle]");

            if (!toggle) {
                return;
            }

            toggle.addEventListener("click", function() {
                if (!mobileChartMedia.matches) {
                    return;
                }

                const isCardExpanded = card.classList.toggle("is-mobile-expanded");

                updateChartToggleLabel(toggle, isCardExpanded);
                window.requestAnimationFrame(() => window.dispatchEvent(new Event("resize")));
            });
        });

        syncChartCards();

        if (typeof mobileChartMedia.addEventListener === "function") {
            mobileChartMedia.addEventListener("change", syncChartCards);
        } else if (typeof mobileChartMedia.addListener === "function") {
            mobileChartMedia.addListener(syncChartCards);
        }
    });
</script>
