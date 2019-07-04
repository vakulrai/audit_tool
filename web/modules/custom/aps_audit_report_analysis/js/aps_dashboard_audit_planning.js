(function ($) {
  'use strict';
  Drupal.behaviors.aps_dashboard_audit_planning_js = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      var options = {
        chart: {
          plotBackgroundColor: null,
          plotBorderWidth: null,
          plotShadow: false,
          type: 'pie'
        },
        title: {
            text: 'Audit report For current Audit cycle April-2019 to April-2020'
          },
          tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
          },
          plotOptions: {
            pie: {
              allowPointSelect: true,
              cursor: 'pointer',
              dataLabels: {
                enabled: false
              },
              showInLegend: true
            }
          },
        series: [{
          name: 'Audit Type',
          colorByPoint: true,
        }]
    };
    
    var url =  base_url + "/audit-coverage-details/get.json";
    $.getJSON(url,  function(data) {
        options.series[0].data = data;
        var chart = new Highcharts.Chart('container',options);
    });

    //Check if filter is applied.
     $('#planning-submit').click(function(event) {
        var audit_type = $("#audit-type").val();
        var start_date = $("#start-date").val();
        var end_date = $("#end-date").val();
        // console.log(audit_type + start_date  + end_date);
         event.preventDefault();
     });
    }
  };
}(jQuery));