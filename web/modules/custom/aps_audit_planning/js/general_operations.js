(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_general_ops = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      var page = drupalSettings.page;
      //Click event for Document Records for Auditor.
      if(page == 'documentrecords'){
        $('select').on('change', function() {
          var record_reference = $(this).attr('record_reference');
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
      }
      
      if(page == 'planned-audit-listing'){
        //Click event for CAR Report update for Auditor.
        $('select').on('change', function() {
          var ncr_reference = $(this).attr('record_reference');
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
    }
  };
}(jQuery));