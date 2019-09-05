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
          console.log(event);
          if (event._def.rendering === 'background' && event._def.url.indexOf('google') === -1){
            return true;
          }
          else if(event._def.rendering === 'background' && event._def.url.indexOf('google') !== -1){
            var event_name = event._def.title +'\n\n Can\'t Proceed !!';
            Swal.fire({
              type: 'info',
              title: event_name,
            })
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
            var current = formatDate(arg.start);
            var start = formatDate(arg.start);
            var end = formatDate(arg.end);
            var get_current_selected_month = arg.start.getMonth() + 1;
            $.ajax({
              url: base_url + '/verify-pressure-months/'+unit_id+'/'+get_current_selected_month,
              async:false,
              success: function (result) {
                if (result == true) {
                  Swal.fire({
                  title: 'The following Date comes under Pressure month.Are you sure you want to contine ?',
                  // text: "You won't be able to revert this!",
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes',
                  cancelButtonText: 'No'
                }).then((result) => {
                  if (result.value) {
                    window.location.href = base_url + '/node/add/planned_events?field_start_date='+start+'&field_end_date='+end+'&current='+current+'&unit_reference='+url_param[2];
                  }
                })
                }
                else{
                  window.location.href = base_url + '/node/add/planned_events?field_start_date='+start+'&field_end_date='+end+'&current='+current+'&unit_reference='+url_param[2];
                }
              }
            });
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
            // info.event._def.rendering = 'background';
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