var CacheActivityChart = function(options) {
    var reloadTimer,
        reloadTimeout = 10000, // 10 seconds
        config = {
            url: null,
            elements: {
                range  : '#range',
                refresh: null,
                canvas : '#chart',
                legend : '#legend'
            },
            chart: {
                options: {
                    bezierCurve: false,
                    responsive : true,
                    animation  : true
                },
                data: {
                    datasets: [{    // Hits
                        fillColor           : "rgba(164, 190, 140, 0.1)",
                        strokeColor         : "rgba(164, 190, 140, 1)",
                        pointColor          : "rgba(164, 190, 140, 1)",
                        pointStrokeColor    : "#fff",
                        pointHighlightFill  : "#fff",
                        pointHighlightStroke: "rgba(164, 190, 140,1)"
                    }, {            // Misses
                        fillColor           : "rgba(208, 98, 65, 0.1)",
                        strokeColor         : "rgba(208, 98, 65, 1)",
                        pointColor          : "rgba(208, 98, 65, 1)",
                        pointStrokeColor    : "#fff",
                        pointHighlightFill  : "#fff",
                        pointHighlightStroke: "rgba(208, 98, 65,1)"
                    }]
                }
            }
        };

    function deepObjectExtend(destination, source) {
        for (var property in source) {
            if (source[property] &&
                source[property].constructor &&
                (source[property].constructor === Object ||
                    source[property].constructor === Array)) {

                if (source[property].constructor === Object) {
                    destination[property] = destination[property] || {};
                    arguments.callee(destination[property], source[property]);
                }
                else if (source[property].constructor === Array) {
                    destination[property] = destination[property] || [];
                    for (var i = 0; i < source[property].length; i++) {
                        if (!destination[property][i]) {
                            destination[property][i] = source[property][i];
                        } else {
                            arguments.callee(destination[property][i], source[property][i]);
                        }
                    }
                }
            } else {
                destination[property] = source[property];
            }
        }
        return destination;
    }

    config = deepObjectExtend(config, options);

    function createChart() {
        var context = $$(config.elements.canvas).first().getContext("2d"),
            chart   = new Chart(context).Line(config.chart.data, config.chart.options);

        if ($$(config.elements.legend).first()) {
            $$(config.elements.legend).first().update(chart.generateLegend());
        }

        reloadTimer = setTimeout(function() {
            reloadChart(false, 'append');
        }, reloadTimeout);

        return chart;
    }
    var chart = createChart();

    function getChartParams() {
        return {
            range: $$(config.elements.range).first().getValue()
        };
    }

    function reloadChart(loaderArea, chartRebuidMode) {
        clearTimeout(reloadTimer);

        var params = getChartParams();
        if (params.range == '1m') {
            reloadTimeout = 10000; // 10s
        } else {
            reloadTimeout = 60000; // 1m
        }

        new Ajax.Request(config.url, {
            parameters: params,
            loaderArea: loaderArea,
            onSuccess : function(response) {
                var data      = JSON.parse(response.responseText),
                    oldLength = chart.datasets[0].points.length,
                    newLength = data.labels.length;

                chartRebuidMode = 'rebuild'; // fix to prevent missing points on chart

                // if ('append' === chartRebuidMode) {
                //     var newLabel = data.labels[newLength - 1],
                //         newHit   = data.hits[newLength - 1],
                //         newMiss  = data.misses[newLength - 1],
                //         oldLabel = chart.datasets[0].points[newLength - 1].label,
                //         oldHit   = chart.datasets[0].points[newLength - 1].value,
                //         oldMiss  = chart.datasets[1].points[newLength - 1].value;

                //     // if no new labels
                //     if (newLabel == oldLabel) {
                //         if (newHit == oldHit && newMiss == oldMiss) {
                //             chartRebuidMode = false; // no changes in last point - do not redraw chart
                //         } else {
                //             chartRebuidMode = 'rebuild';
                //         }
                //     }
                // }

                switch (chartRebuidMode) {
                    case 'rebuild':
                        chart.options.animation = false;
                        for (var i = 0; i < data.labels.length; i++) {
                            chart.addData(
                                [
                                    data.hits[i],
                                    data.misses[i]
                                ],
                                data.labels[i]
                            );
                        }
                        while (oldLength--) {
                            chart.removeData();
                        }
                        break;
                    case 'append':
                        chart.options.animation = true;
                        chart.addData(
                            [
                                data.hits[newLength - 1],
                                data.misses[newLength - 1]
                            ],
                            data.labels[newLength - 1]
                        );
                        chart.removeData();
                        break;
                }

                reloadTimer = setTimeout(function() {
                    reloadChart(false, 'append');
                }, reloadTimeout);
            }
        });
    }

    $$(config.elements.range).first().observe('change', function() {
        reloadChart($$('.chart-wrapper').first(), 'rebuild');
    });

    return {
        reload: function(loaderArea, chartRebuidMode) {
            chartRebuidMode = chartRebuidMode || 'rebuild';
            loaderArea      = loaderArea || false;

            reloadChart(loaderArea, chartRebuidMode);
        }
    };
};
