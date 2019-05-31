(function ($) {
  'use strict';
  Drupal.behaviors.aps_audit_criteria = {
    attach: function (context, settings) {
      $('input[name="field_score_settings"]').click(function() {
        var id = $(this).val();
        if(id == '32'){
          $("div.field--name-field-score-level-set-100").hide();
          $("#edit-field-score-level-set-wrapper").show();
        }
        else{
          $("div.field--name-field-score-level-set").hide();
          $("#edit-field-score-level-set-100-wrapper").show();
        }
      });
    }
  };
}(jQuery));