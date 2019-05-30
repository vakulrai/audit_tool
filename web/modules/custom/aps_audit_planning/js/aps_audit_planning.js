(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_planning = {
    attach: function (context, settings) {
    	var calendarEl = document.getElementById('demo');
      var base_url = drupalSettings.siteBaseUrl;
      var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid', 'timeGrid' ],
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      navLinks: true,
      selectable: true,
      selectMirror: true,
      events: {
        url: base_url+'/generate-events',
        failure: function() {
        }
      },
      select: function(arg) {
        console.log(arg);
        var title = prompt('Enter A Title:');
        if (title) {
          var current = formatDate(arg.start);
          var start = formatDate(arg.start);
          var end = formatDate(arg.end);
          var url = base_url + '/node/add/planned_events?field_start_date='+start+'&field_end_date='+end+'&current='+current+'&title='+title;
          calendar.addEvent({
            title: title,
            start: arg.start,
            url: url,
            end: arg.end,
            allDay: arg.allDay,
            editable: false,
          })
        }
        // calendar.unselect()
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