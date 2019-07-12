(function ($) {
  'use strict';
  Drupal.behaviors.risk_management_js = {
    attach: function (context, settings) {
      // var audit_detail = drupalSettings.auditDetail;
      var gaugeOptions = {

		    chart: {
		        type: 'solidgauge'
		    },

		    title: 'Risk Meter',

		    pane: {
		        center: ['30%', '90%'],
		        size: '70%',
		        startAngle: -90,
		        endAngle: 90,
		        background: {
		            backgroundColor:
		                Highcharts.defaultOptions.legend.backgroundColor || '#EFC',
		            innerRadius: '60%',
		            outerRadius: '100%',
		            shape: 'arc'
		        }
		    },

		    tooltip: {
		        enabled: false
		    },

		    // the value axis
		    yAxis: {
		        // stops: [
		        //     [0.1, '#55BF3B'], // green
		        //     [0.5, '#DDDF0D'], // yellow
		        //     [0.9, '#DF5353'] // red
		        // ],
		        lineWidth: 0,
		        minorTickInterval: null,
		        tickAmount: 2,
		        title: {
		            y: -70
		        },
		        labels: {
		            y: 16
		        }
		    },

		    plotOptions: {
		        solidgauge: {
		            dataLabels: {
		                y: 5,
		                borderWidth: 0,
		                useHTML: true
		            }
		        }
		    }
		};

		// The speed gauge
		var chartSpeed = Highcharts.chart('container-element-risk-report', Highcharts.merge(gaugeOptions, {
		    yAxis: {
		        min: 0,
		        max: 100,
		        title: {
		            text: ''
		        }
		    },

		    credits: {
		        enabled: false
		    },

		    series: [{
		        name: 'Speed',
		        data: [80],
		        dataLabels: {
		            format:
		                '<div style="text-align:center">' +
		                '<span style="font-size:25px">{y}</span><br/>' +
		                '<span style="font-size:12px;opacity:0.4">%</span>' +
		                '</div>'
		        },
		        tooltip: {
		            valueSuffix: ' %'
		        }
		    }]

		}));

		// The RPM gauge
		// Bring life to the dials
		// setInterval(function () {
		//     // Speed
		//     var point,
		//         newVal,
		//         inc;

		//     if (chartSpeed) {
		//         point = chartSpeed.series[0].points[0];
		//         inc = Math.round((Math.random() - 0.5) * 100);
		//         newVal = point.y + inc;

		//         if (newVal < 0 || newVal > 200) {
		//             newVal = point.y - inc;
		//         }

		//         point.update(newVal);
		//     }

		//     // RPM
		//     if (chartRpm) {
		//         point = chartRpm.series[0].points[0];
		//         inc = Math.random() - 0.5;
		//         newVal = point.y + inc;

		//         if (newVal < 0 || newVal > 5) {
		//             newVal = point.y - inc;
		//         }

		//         point.update(newVal);
		//     }
		// }, 2000);

    }
  };
}(jQuery));