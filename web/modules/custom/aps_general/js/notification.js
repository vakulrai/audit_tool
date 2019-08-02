(function ($) {
  'use strict';
  Drupal.behaviors.aps_general_notification_js = {
    attach: function (context, settings) {
      var base_url = drupalSettings.siteBaseUrl;
      $('#block-adminmainmenu-4 #superfish-admin-main-menu li .notify').click(function(e){
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