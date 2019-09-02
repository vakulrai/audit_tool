(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_planning = {
    attach: function (context, settings) {
      var pathname = window.location.pathname;
      var url_param = pathname.split("/");
    	var calendarEl = document.getElementById('demo');
      var base_url = drupalSettings.siteBaseUrl;
      var unit_id = drupalSettings.unitId;

      var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'googleCalendar'],
        googleCalendarApiKey: 'AIzaSyDAhieDD1LZNmnuFROsxhJQAGZP6amv-cg',
        header: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        contentHeight: 600,
        navLinks: true,
        selectable: true,
        selectMirror: true,
        selectOverlap: function(event) {
          if (event._def.rendering === 'background' && event._def.url.indexOf('google') === -1){
            if (confirm('The following Date comes under Pressure month.Are you sure you want to contine ?')) {
              var title = prompt('Enter Event Title');
              if (title) {
                var today = event._instance.range.start;
                var dd = today.getDate(); var mm = today.getMonth()+1;var yyyy = today.getFullYear();
                var pressure_month_start = yyyy+'-'+mm+'-'+dd;
                var pressure_month_end_date = event._instance.range.end.getMonth() + 1;
                var pressure_month_end = event._instance.range.end.getFullYear()+'-'+pressure_month_end_date+'-'+event._instance.range.end.getDate();
                var current = pressure_month_start;
                var start = formatDate(current);
                var end = formatDate(pressure_month_end);
                window.location.href = base_url + '/node/add/planned_events?field_start_date='+start+'&field_end_date='+end+'&current='+current+'&unit_reference='+url_param[2];
              }
            } 
            else {
            }
          }
          else if(event._def.rendering === 'background' && event._def.url.indexOf('google') !== -1){
            var event_name = event._def.title +'\n\n Can\'t Proceed !!';
            alert(event_name);
            return false;
          }
          else{
            return true;
          }
        },
        validRange: function(nowDate) {
          var startDate = new Date(nowDate.valueOf());
          var endDate = new Date(nowDate.valueOf());
          var month_range = 12 - nowDate.getMonth();
          startDate.setDate(nowDate.getDate()); // One day in the past
          endDate.setMonth(nowDate.getMonth()+ month_range); // one month into the future
          return { start: startDate, end: endDate };
        },
        eventSources: [
          base_url+'/generate-events/'+unit_id,
          base_url+'/list-of-unit-holidays/'+unit_id,
          base_url+'/get-pressure-months/'+unit_id,
          {
            googleCalendarId: 'en.indian#holiday@group.v.calendar.google.com',
            textColor: 'white',
            className: 'gcal-event',
            rendering: 'background',
            color: '#94d82d'
          }
        ],
        select: function(arg) {
          // var title = prompt('Enter A Title:');
          // if (title) {
            var current = formatDate(arg.start);
            var start = formatDate(arg.start);
            var end = formatDate(arg.end);
            window.location.href = base_url + '/node/add/planned_events?field_start_date='+start+'&field_end_date='+end+'&current='+current+'&unit_reference='+url_param[2];
            // calendar.addEvent({
            //   title: title,
            //   start: arg.start,
            //   url: url,
            //   end: arg.end,
            //   allDay: false,
            //   editable: false,
            // })
          // }
        },
        eventRender: function(info) {
          if (info.event._def.rendering === 'background'){
            var tooltip = new Tooltip(info.el, {
              title: info.event._def.extendedProps.description,
              placement: 'top',
              trigger: 'hover',
              container: 'body'
            });
          }
          if(info.event._def.url.indexOf('google') !== -1){
            info.event._def.rendering = 'background';
            info.event._def.ui.color = '#fff85b';
          }
        },
        eventLimit: true,
      });
      calendar.render();

      function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
      }
    }
  };
}(jQuery));