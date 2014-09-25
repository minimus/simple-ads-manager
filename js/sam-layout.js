(function($) {
  $(document).ready(function() {
    var hits = [];
    if(samAjax.load) {
      if(samAjax.mailer) $.post(samAjax.ajaxurl, {action: 'sam_maintenance'});

      // Loading Ads
      var ads = [];
      $('div.sam-place').each(function(i, el) {
        var codes = $(el).data('sam');
        if('undefined' == typeof codes) codes = 0;
        var
          ids = this.id.split('_'),
          id = ids[1],
          pid = ids[2];

        ads.push([pid, id, codes, this.id]);
      });

      if(ads.length > 0) {
        $.post(samAjax.loadurl, {
          action: 'load_ads',
          ads: ads,
          wc: samAjax.clauses,
          level: samAjax.level
        }).done(function (data) {
          if (data.success) {
            var hits = [];
            $.each(data.ads, function (i, ad) {
              $('#' + ad.eid).replaceWith(ad.ad);
              $('#' + ad.cid).find('a').bind('click', function (e) {
                $.post(samAjax.ajaxurl, {
                  action: 'sam_click',
                  id: ad.id,
                  pid: ad.pid,
                  level: samAjax.level
                });
              });
              hits.push([ad.pid, ad.id]);
            });
            if (hits.length > 0) {
              $.post(samAjax.ajaxurl, {
                action: 'sam_hits',
                hits: hits,
                level: samAjax.level
              });
            }
          }
        });
      }

      // Ads loaded by PHP
      $('div.sam-ad').each(function(i, el) {
        var
          ids = this.id.split('_'),
          id = ids[1],
          pid = ids[2];

        hits.push([pid, id]);

        $(el).find('a').bind('click', function(e) {
          $.post(samAjax.ajaxurl, {
            action: 'sam_click',
            id: id,
            pid: pid,
            level: samAjax.level
          });
        });
      });

      if(hits.length > 0) {
        $.post(samAjax.ajaxurl, {
          action: 'sam_hits',
          hits: hits,
          level: samAjax.level
        });
      }
    }
    else {
      $('div.sam-container').each(function(i, el) {
        var
          ids = this.id.split('_'),
          id = ids[1],
          pid = ids[2];

        hits.push([pid, id]);

        $(el).find('a').bind('click', function(e) {
          $.post(samAjax.ajaxurl, {
            action: 'sam_click',
            id: id,
            pid: pid,
            level: samAjax.level
          });
        });
      });

      if(hits.length > 0) {
        $.post(samAjax.ajaxurl, {
          action: 'sam_hits',
          hits: hits,
          level: samAjax.level
        });
      }
    }
  });
})(jQuery);