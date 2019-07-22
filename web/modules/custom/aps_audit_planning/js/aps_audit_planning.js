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
        if (event._def.url.indexOf('google') > -1){
           return event.rendering === 'background';
        }
      },
      eventSources: [
        base_url+'/generate-events',
        base_url+'/list-of-unit-holidays/'+unit_id,
        {
          googleCalendarId: 'en.indian#holiday@group.v.calendar.google.com',
          color: 'blue',
          textColor: 'white',
          className: 'gcal-event',
          rendering: 'background',
          overlap: true,
        }
      ],
      select: function(arg) {
        var title = prompt('Enter A Title:');
        if (title) {
          var current = formatDate(arg.start);
          var start = formatDate(arg.start);
          var end = formatDate(arg.end);
          var url = base_url + '/node/add/planned_events?field_start_date='+start+'&field_end_date='+end+'&current='+current+'&title='+title+'&unit_reference='+url_param[2];
          calendar.addEvent({
            title: title,
            start: arg.start,
            url: url,
            end: arg.end,
            allDay: arg.allDay,
            editable: false,
          })
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