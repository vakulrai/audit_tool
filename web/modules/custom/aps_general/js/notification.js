(function ($) {
  'use strict';
  Drupal.behaviors.aps_general_notification_js = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      var notify_id = drupalSettings.notify_id;
      var block_id = drupalSettings.block_id;
      $(block_id +' '+notify_id+' li .notify').click(function(e){
          $.ajax({
          url: base_url+"/update-notifications",
          type: "POST",
          processData:false,
          success: function(data){
          },
          error: function(){}           
        });
      });
    }
  };
}(jQuery));