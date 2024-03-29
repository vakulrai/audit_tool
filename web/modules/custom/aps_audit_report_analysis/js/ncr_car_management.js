(function ($) {
  'use strict';
  Drupal.behaviors.ncr_car_js = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      var audit_detail = drupalSettings.auditDetail;
      var unit_reference = drupalSettings.unitReference;
      var ncr_car_data = drupalSettings.ncr_car_data;
      var chart_a = Highcharts.chart('container-element-ncr-car', {
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
		        text: 'NCR and CAR Management<br>Total: '+ncr_car_data['total'],
		    },
		    tooltip: {
		        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
		    },
		    plotOptions: {
		        pie: {
		            allowPointSelect: true,
		            cursor: 'pointer',
		            dataLabels: {
		                enabled: true,
		                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
		            }
		        }
		    },
		    series: [{
		        name: 'NCR vs CAR',
		        colorByPoint: true,
		        data: [{
		            name: 'Completed<br>'+ncr_car_data['completed'],
		            y: Math.round((ncr_car_data['completed'])/ncr_car_data['total'] * 100),
		            sliced: true,
		            selected: true
		        }, {
		            name: 'Pending<br>'+ncr_car_data['pending'],
		            y: Math.round((ncr_car_data['pending'])/ncr_car_data['total'] * 100),
		            color: 'red',
		        }]
		    }],
		    lang: {
              noData: ''
            },
            noData: {
		        style: {
		            fontWeight: 'bold',
		            fontSize: '20px',
		            color: '#3980d1'
		        }
            }
		});

        if(ncr_car_data['total'] == 0){
          chart_a.showNoData('No data Found');
        }
    }
  };
}(jQuery));