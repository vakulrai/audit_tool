(function ($) {
  'use strict';
  Drupal.behaviors.risk_management_js = {
    attach: function (context, settings) {
      var risk_percentage = drupalSettings.risk_percentage;
      var risk_type = drupalSettings.risk_type;
      var color = '';

      if(risk_percentage > 0 && risk_percentage < 33.33 && risk_type == 'LOW'){
        color = '#3980D1';
      }
      else if(risk_percentage >= 33.33 && risk_percentage <= 66.66 && risk_type == 'MEDIUM'){
        color = '#55616E';
      }
      else{
        color = '#DF5353';
      }
      var gaugeOptions = {

		    chart: {
		        type: 'solidgauge'
		    },
		    title: {
              text: 'Risk Meter<br>'+risk_type,
              align: 'center',
              verticalAlign: 'middle',
              y: 50
            },

		    pane: {
		        center: ['50%', '75%'],
		        size: '100%',
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
		        enabled: true
		    },

		    // the value axis
		    yAxis: {
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
		            },
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
		        name: 'Risk',
		        data: [risk_percentage],
		        colors: [color],
		        selected: true,
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
        
        var legend = Highcharts.chart('container-element-risk-legend',{
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false
        },
        title: {
            text: 'Risk Scale',
            align: 'center',
            verticalAlign: 'middle',
            y: 50
        },
        tooltip: {
		    enabled: false
		},
        plotOptions: {
            pie: {
            	allowPointSelect: true,
                dataLabels: {
                    enabled: true,
                    distance: -50, 
                    // formatter: function() {
                    //     var dlabel = this.point.name + '<br/>';
                    //     dlabel += Math.round(this.percentage*100)/100 + ' %';
                    //     return dlabel
                    // },
                    style: {
                        fontWeight: 'bold',
                        color: 'white',
                        textShadow: '0px 1px 2px black'
                    }
                },
                startAngle: -90,
                endAngle: 90,
                center: ['50%', '75%']
            },
        },
        series: [{
            type: 'pie',
            name: 'Risk Score',
            innerSize: '50%',
            data: [
                ['LOW: 1',33.7],
                ['MODERATE: 3',33.7],
                ['HIGH: 5',33.7],    
            ],
            colors: ['#3980D1', '#55616E', '#DF5353']
        }]
    });
        
    }
  };
}(jQuery));