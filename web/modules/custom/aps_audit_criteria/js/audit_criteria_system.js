(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_criteria = {
    attach: function (context, settings) {
      $('input[name="field_score_settings"]').click(function() {
        var id = $(this).val();
        if(id == '32'){
          $("#set-100").hide();
          $("#set-10").show();

        }
        else if(id == '33'){
          $("#set-10").hide();
          $("#set-100").show();
        }
        else{
          $("#set-10").hide();
          $("#set-100").hide();
        }
      });
    }
  };
}(jQuery));