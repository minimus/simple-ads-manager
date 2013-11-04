(function($) {
  $(document).ready(function() {
    $(".sam_ad").click(function(e) {
      var
        adId = $(this).attr('id'),
        url = this.href,
        target = $(this).attr('target');
      $.ajax({
        type: "POST",
        url: samAjax.ajaxurl,
        data: {
          action: "sam_click", 
          sam_ad_id: adId,
          level: samAjax.level
        },
        async: true
      });
      setTimeout(function() {
        if(target == '_blank') window.open(url);
        else window.location = url;
      }, 100);

      e.preventDefault();
    });
  });
})(jQuery);