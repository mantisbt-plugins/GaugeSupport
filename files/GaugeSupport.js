/* jshint -W097 */
"use strict";

$(document).ready( function() {

    $('#issue_gauge').each( function() {
        resizeCanvas();

        var chart = new Chart($(this), {
            type: 'doughnut',
            data: {
                labels: $(this).data('labels'),
                datasets: [{
                    data: $(this).data('values'),
                    borderWidth: 1,
                    backgroundColor: ['#d7191c', '#fdae61', '#a6d96a', '#1a9641']
                }]
            },
            options: {
                legend: {
                    position: 'right'
                },
                maintainAspectRatio: false
            }
        });
    });

    $(window).on('resize', function() {
        resizeCanvas();
    });

    /**
     * Dynamically size the canvas based on height of rankings div
     */
    function resizeCanvas() {
        $("#issue_gauge").parent().innerHeight($("#gauge_rankings").height());
    }
});
