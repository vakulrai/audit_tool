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
      defaultDate: '2019-04-12',
      navLinks: true, // can click day/week names to navigate views
      selectable: true,
      selectMirror: true,
      // events: {
      //   url: base_url+'/get-events',
      //   failure: function() {
      //   }
      // },
      select: function(arg) {
        var title = prompt('Enter A Title:');
        if (title) {
          calendar.addEvent({
            title: title,
            start: arg.start,
            url: 'http://google.com/',
            end: arg.end,
            allDay: arg.allDay,
            editable: false,
          })
          $.ajax({
            type: "post",
            url: base_url + "/generate-events",
            async: false,
            data: {field_start_date: arg.start, field_end_date: arg.start, title: title},
          success: function (result) {
          }
        });
        }
        // calendar.unselect()
      },
      eventLimit: true, // allow "more" link when too many events
      // events: [
      //   {
      //     title: 'All Day Event',
      //     start: '2019-04-01'
      //   },
      //   {
      //     title: 'Long Event',
      //     start: '2019-04-07',
      //     end: '2019-04-10'
      //   },
      //   {
      //     groupId: 999,
      //     title: 'Repeating Event',
      //     start: '2019-04-09T16:00:00'
      //   },
      //   {
      //     groupId: 999,
      //     title: 'Repeating Event',
      //     start: '2019-04-16T16:00:00'
      //   },
      //   {
      //     title: 'Conference',
      //     start: '2019-04-11',
      //     end: '2019-04-13'
      //   },
      //   {
      //     title: 'Meeting',
      //     start: '2019-04-12T10:30:00',
      //     end: '2019-04-12T12:30:00'
      //   },
      //   {
      //     title: 'Lunch',
      //     start: '2019-04-12T12:00:00'
      //   },
      //   {
      //     title: 'Meeting',
      //     start: '2019-04-12T14:30:00'
      //   },
      //   {
      //     title: 'Happy Hour',
      //     start: '2019-04-12T17:30:00'
      //   },
      //   {
      //     title: 'Dinner',
      //     start: '2019-04-12T20:00:00'
      //   },
      //   {
      //     title: 'Birthday Party',
      //     start: '2019-04-13T07:00:00'
      //   },
      //   {
      //     title: 'Click for Google',
      //     url: 'http://google.com/',
      //     start: '2019-04-28',
      //     editable: true
      //   }
      // ]
    });
      calendar.render();
    }
  };
}(jQuery));