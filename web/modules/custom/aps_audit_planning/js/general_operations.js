(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_general_ops = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      //Click event for Document Records for Auditor.
      $('#kpi-details').on('change', function() {
        var record_reference = $('#kpi-details').attr('record_reference');
        $.ajax({
          url: base_url + '/update-kpi-info',
          data: {record_reference: record_reference, value_selected: this.value, type: 'record'},
          success: function (result) {
            if (result) {
              console.log(result);
            }
          }
        });
      });

      //Click event for CAR Report update for Auditor.
      $('#ncr-details').on('change', function() {
        var ncr_reference = $('#ncr-details').attr('record_reference');
        $.ajax({
          url: base_url + '/update-kpi-info',
          data: {record_reference: ncr_reference, value_selected: this.value, type: 'car'},
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