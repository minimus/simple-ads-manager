jQuery(document).ready(function($) {
    $(".sam_ad").click(function() {
      var adId = $(this).attr('id');
      var data = { action: 'sam_click', sam_ad_id: adId, _ajax_nonce: samAjax._ajax_nonce };
      jQuery.post(samAjax.ajaxurl, data, function(data){
        alert("Data Loaded: " + data);
      });
    });
  });