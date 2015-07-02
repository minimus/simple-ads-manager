(function($) {
  $(document).ready(function() {
    var hits = [], doStats = ('string' == typeof samAjax.doStats) ? Number(samAjax.doStats) : samAjax.doStats;
    if(samAjax.mailer) $.post(samAjax.ajaxurl, {action: 'sam_maintenance'});
    if(samAjax.load) {
      // Loading Ads
      var ads = [];
      $('div.' + samAjax.place).each(function(i, el) {
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
          wc: samAjax.clauses
        }).done(function (data) {
          if (data.success) {
            var hits = [];
            $.each(data.ads, function (i, ad) {
              $('#' + ad.eid).replaceWith(ad.ad);
              if(doStats) {
                $('#' + ad.cid).find('a').bind('click', function (e) {
                  $.post(samAjax.ajaxurl, {
                    action: 'sam_click',
                    id: ad.id,
                    pid: ad.pid
                  });
                });
                hits.push([ad.pid, ad.id]);
              }
            });
            if (hits.length > 0 && doStats) {
              $.post(samAjax.ajaxurl, {
                action: 'sam_hits',
                hits: hits
              });
            }
          }
        });
      }

      // Ads loaded by PHP
      if(doStats) {
        $('div.' + samAjax.ad).each(function (i, el) {
          var
            ids = this.id.split('_'),
            id = ids[1],
            pid = ids[2];

          hits.push([pid, id]);

          $(el).find('a').bind('click', function (e) {
            $.post(samAjax.ajaxurl, {
              action: 'sam_click',
              id: id,
              pid: pid
            });
          });
        });

        if (hits.length > 0) {
          $.post(samAjax.ajaxurl, {
            action: 'sam_hits',
            hits: hits
          });
        }
      }
    }
    else {
      if(doStats) {
        $('div.' + samAjax.container).each(function (i, el) {
          var
            ids = this.id.split('_'),
            id = ids[1],
            pid = ids[2];

          hits.push([pid, id]);

          $(el).find('a').bind('click', function (e) {
            $.post(samAjax.ajaxurl, {
              action: 'sam_click',
              id: id,
              pid: pid
            });
          });
        });

        if (hits.length > 0) {
          $.post(samAjax.ajaxurl, {
            action: 'sam_hits',
            hits: hits
          });
        }
      }
    }
  });
})(jQuery);