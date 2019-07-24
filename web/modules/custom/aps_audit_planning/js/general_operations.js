(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_general_ops = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      $('#kpi-details').on('change', function() {
        var record_reference = $('#kpi-details').attr('record_reference');
        $.ajax({
          url: base_url + '/update-kpi-info',
          data: {record_reference: record_reference, value_selected: this.value},
          success: function (result) {
            if (result) {
              console.log(result);
            }
          }
        });
      });
    }
  };
}(jQuery));