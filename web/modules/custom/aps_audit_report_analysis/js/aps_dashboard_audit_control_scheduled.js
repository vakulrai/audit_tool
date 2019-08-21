(function ($) {
  'use strict';
  Drupal.behaviors.aps_dashboard_audit_control_scheduled_js = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      var audit_detail = drupalSettings.data;
      var product_data = drupalSettings.product_data;
      var process_data = drupalSettings.process_data;
      var procedure_data = drupalSettings.procedure_data;
      var total_product_covered = 0;
      var total_product_not = 0;
      var total_process_covered = 0;
      var total_process_not = 0;
      var total_procedure_covered = 0;
      var total_procedure_not = 0;

      if(product_data['total'] == 0){
        total_product_covered = 0;
        total_product_not = 100;
      }
      else{
      	total_product_covered = Math.round(product_data['completed']/product_data['total'] * 100);
      	total_product_not = Math.round((product_data['total'] - product_data['completed'])/product_data['total'] * 100);
      }

      if(process_data['total'] == 0){
        total_process_covered = 0;
        total_process_not = 100;
      }
      else{
      	total_process_covered = Math.round(process_data['completed']/process_data['total'] * 100);
      	total_process_not = Math.round((process_data['total'] - process_data['completed'])/process_data['total'] * 100);
      }

      if(procedure_data['total'] == 0){
        total_procedure_covered = 0;
        total_procedure_not = 100;
      }
      else{
      	total_procedure_covered = Math.round(procedure_data['completed']/procedure_data['total'] * 100);
      	total_procedure_not = Math.round((procedure_data['total'] - procedure_data['completed'])/procedure_data['total'] * 100);
      }
      var chart_a = new Highcharts.Chart('container-scheduled-a',{
      	  credits: {
            enabled: false
          },
		  chart: {
		    // renderTo: 'container-scheduled-a',
		    type: 'bar',
            "height": 200,
		  },
		  title: {
            text: 'Products'
          },
		  legend: {
		    enabled: true
		  },
		  colors: ['#173c64'],
		  xAxis: {
            lineWidth: 0,
		    minorGridLineWidth: 0,
		    lineColor: 'transparent',
		    minorTickLength: 0,

		    tickLength: 0,
		    labels: {
		      enabled: false
		    }
		  },
		  yAxis: {
		    min: 0,
		    title: '',
		    max: 100,
		    offset: -60,
		    gridLineColor: '',
		    labels: {
		      format: '{value}%'
		    },
		    tickPositions: [0, 100],
		    enabled: true
		  },
		  plotOptions: {
		    bar: {
		      stacking: "normal",
		      //groupPadding: 0, //add here
		      //pointPadding: 0, //add here,
		      dataLabels: {
		        enabled: true,
		        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
		      },
		    },
		  },
		  series: [{
		    "name": "Products covered",
		    "data": [total_product_covered],
		    "color": "#33fff9",
		    "showInLegend": true
		  },
		  {
		    "name": "Products not covered",
		    "data": [total_product_not],
		    "color": "#ff8c1a",
		    "showInLegend": true	
		  }]
		});

      var chart_b = new Highcharts.Chart({
      	  credits: {
            enabled: false
          },
		  chart: {
		    renderTo: 'container-scheduled-b',
		    type: 'bar',
            "height": 200,
		  },
		  title: {
            text: 'Processes'
          },
		  legend: {
		    enabled: true
		  },
		  colors: ['#173c64'],
		  xAxis: {
            lineWidth: 0,
		    minorGridLineWidth: 0,
		    lineColor: 'transparent',
		    minorTickLength: 0,

		    tickLength: 0,
		    labels: {
		      enabled: false
		    }
		  },
		  yAxis: {
		    min: 0,
		    title: '',
		    max: 100,
		    offset: -60,
		    gridLineColor: '',
		    labels: {
		      format: '{value}%'
		    },
		    tickPositions: [0, 100],
		    enabled: true
		  },
		  plotOptions: {
		    bar: {
		      stacking: "normal",
		      //groupPadding: 0, //add here
		      //pointPadding: 0, //add here,
		      dataLabels: {
		        enabled: true,
		        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
		      }
		    }
		  },
		  series: [{
		    "name": "Process covered",
		    "data": [total_process_covered],
		    "color": "#33fff9",
		    "showInLegend": true
		  },
		  {
		   "name": "Process not covered",
		    "data": [total_process_not],
		    "color": "#ff8c1a",
		    "showInLegend": true	
		  }]
		})

      var chart_c = new Highcharts.Chart({
      	  credits: {
            enabled: false
          },
		  chart: {
		    renderTo: 'container-scheduled-c',
		    type: 'bar',
            "height": 200,
		  },
		  title: {
            text: 'Procedures'
          },
		  legend: {
		    enabled: true
		  },
		  colors: ['#173c64'],
		  xAxis: {
            lineWidth: 0,
		    minorGridLineWidth: 0,
		    lineColor: 'transparent',
		    minorTickLength: 0,

		    tickLength: 0,
		    labels: {
		      enabled: false
		    }
		  },
		  yAxis: {
		    min: 0,
		    title: '',
		    max: 100,
		    offset: -60,
		    gridLineColor: '',
		    labels: {
		      format: '{value}%'
		    },
		    tickPositions: [0, 100],
		    enabled: true
		  },
		  plotOptions: {
		    bar: {
		      stacking: "normal",
		      //groupPadding: 0, //add here
		      //pointPadding: 0, //add here,
		      dataLabels: {
		        enabled: true,
		        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
		      }
		    }
		  },
		  series: [{
		    "name": "Procedures Covered",
		    "data": [total_procedure_covered],
		    "color": "#33fff9",
		    "showInLegend": true
		  },
		  {
		    "name": "Procedures not Covered",
		    "data": [total_procedure_not],
		    "color": "#ff8c1a",
		    "showInLegend": true	
		  }]
		})
    }
  };
}(jQuery));