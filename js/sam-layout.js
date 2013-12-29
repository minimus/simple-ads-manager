(function($) {
  $(document).ready(function() {
    if(samAjax.load) {
      $('div.sam-place').each(function(i, el) {
        var codes = $(el).data('sam');
        if('undefined' == typeof codes) codes = 0;
        var
          ids = this.id.split('_'),
          id = ids[1],
          pid = ids[2];
        $.ajax({
          url: samAjax.loadurl,
          data: {
            action: 'load_place',
            id: id,
            pid: pid,
            codes: codes,
            wc: samAjax.clauses,
            level: samAjax.level
          },
          type: 'POST',
          crossDomain: true
          //dataType: 'jsonp'
        }).done(function(data) {
          $(el).replaceWith(data.ad);
          $.post(samAjax.ajaxurl, {
            action: 'sam_hit',
            id: data.id,
            pid: data.pid,
            level: samAjax.level
          });
          $('#' + data.cid).find('a').bind('click', function(e) {
            $.post(samAjax.ajaxurl, {
              action: 'sam_click',
              id: data.id,
              pid: data.pid,
              level: samAjax.level
            });
          });
        });
      });

      $('div.sam-ad').each(function(i, el) {
        var
          ids = this.id.split('_'),
          id = ids[1],
          pid = ids[2];
        $.post(samAjax.ajaxurl, {
          action: 'sam_hit',
          id: id,
          pid: pid,
          level: samAjax.level
        });
        $(el).find('a').bind('click', function(e) {
          $.post(samAjax.ajaxurl, {
            action: 'sam_click',
            id: id,
            pid: pid,
            level: samAjax.level
          });
        });
      });
    }
    else {
      $('div.sam-container').each(function(i, el) {
        var
          ids = this.id.split('_'),
          id = ids[1],
          pid = ids[2];
        $.post(samAjax.ajaxurl, {
          action: 'sam_hit',
          id: id,
          pid: pid,
          level: samAjax.level
        });

        $(el).find('a').bind('click', function(e) {
          $.post(samAjax.ajaxurl, {
            action: 'sam_click',
            id: id,
            pid: pid,
            level: samAjax.level
          });
        });
      });
    }
  });
})(jQuery);