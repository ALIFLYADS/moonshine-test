@props([
    'title' => '',
    'values' => [],
    'labels' => [],
    'colors' => [],
])

<div
    {{ $attributes->merge(['class' => 'chart']) }}
    x-data="charts({
                series: {{ json_encode($values) }},
                colors: {{ json_encode($colors) }},
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return `${val}`
                        },
                        title: {
                            formatter: function (seriesName) {
                                return `${seriesName}:`
                            },
                        },
                    },
                },
                labels: {{ json_encode($labels) }},
                chart: {
                    height: 350,
                    type: 'donut',
                },
                stroke: {
                    colors: ['transparent'],
                },
                plotOptions: {
                    pie: {
                        expandOnClick: false,
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    label: '{{ $title }}',
                                    showAlways: false,
                                    show: true,
                                    formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                      return Number((a + b).toFixed(10))
                                    }, 0)
                                  }
                                }
                            }
                        }
                    },
                },
            })"
></div>
