(function ($) {
  'use strict';
  Drupal.behaviors.aps_dashboard_audit_planning_js = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      var audit_detail = drupalSettings.auditDetail;
      var unit_reference = drupalSettings.unitReference;
      var chart = new Highcharts.Chart('container', { 
        credits: {
          enabled: false
        },
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
          data: audit_detail
        }],
        lang: {
          noData: 'No data Found'
        },
        noData: {
        style: {
            fontWeight: 'bold',
            fontSize: '20px',
            color: '#3980d1'
        }
    }
    });

    //Check if filter is applied.
     $('#planning-submit').click(function() {
        var audit_type = $("#audit-type").val();
        var start_date = $("#start-date").val();
        var end_date = $("#end-date").val();
        var url =  base_url + "/audit-coverage-details/get.json?audit_type="+audit_type+"&unit_reference="+unit_reference;
        $.getJSON(url,  function(data) {
          chart.series[0].setData(data);
        });
        event.preventDefault();
     });
    }
  };
}(jQuery));