(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_criteria = {
    attach: function (context, settings) {
      $('input[name="field_cycle_type"]').click(function() {
        var id = $(this).val();
        if(id == '0'){
          $("div.field--name-field-calendar-date").hide();
          $("#edit-field-financial-dates-wrapper").show();
        }
        else{
          $("div.field--name-field-financial-dates").hide();
          $("#edit-field-calendar-date-wrapper").show();
        }
      });
    }
  };
}(jQuery));