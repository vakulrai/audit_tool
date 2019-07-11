(function ($) {
  'use strict';
  Drupal.behaviors.auditor_report_js = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      var auditor_data = JSON.parse(drupalSettings.auditor_data);
      var total_user = drupalSettings.total_user;
      var selected_user_count = drupalSettings.selected_user_count;
      var percentage_auditor = Math.round(selected_user_count/total_user * 100)
      Highcharts.chart('container-element-auditor-report', {
		    chart: {
		        type: 'line'
		    },
		    title: {
		        text: percentage_auditor+'% Auditor Selection'
		    },
		    subtitle: {
		        text: ''
		    },
		    xAxis: {
		        categories:  ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
		    },
		    yAxis: {
		        title: {
		            text: 'Underscore Auditor'
		        }
		    },
		    tooltip: {
	        headerFormat: '<b>{series.name}</b><br />',
	        pointFormat: 'Month = {point.x}, Auditor Used = {point.y}'
            },
		    plotOptions: {
		        line: {
		            dataLabels: {
		                enabled: true
		            },
		            enableMouseTracking: true
		        }
		    },
		    series: [{
		    	name: 'Underscore User',
                data: auditor_data
		    }]
		});
    }
  };
}(jQuery));