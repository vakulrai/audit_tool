(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_criteria = {
    attach: function (context, settings) {
      $('input[name="field_cycle_type"]').click(function() {
        var id = $(this).val();
        if(id == '0'){
          $("div.field--type-datetime").hide();
          $("#edit-field-financial-dates-wrapper").show();
        }
        else{
          $("div.field--type-daterange").hide();
          $("#edit-field-ca-wrapper").show();
        }
      });
    }
  };
}(jQuery));