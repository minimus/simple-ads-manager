(function($) {
  $(document).ready(function() {
    $('.more-info').click(function() {
      var
        id = $(this).attr('id').split('-'),
        idn = id[1];
      $.post(options.ajaxurl, {
        action: 'get_error',
        id: idn,
        wa: options.warning,
        ue: options.update,
        oe: options.output
      }).done(function(data) {
          var
            eType = data.type,
            eDate = data.date,
            eTable = data.name,
            eMsg = data.msg,
            eSql = data.es,
            eResolved = data.resolved,
            img = options.imgURL + ((eResolved-0) ? 'ok.png' : 'warning.png'),
            alt = options.alts[eResolved-0],
            dHTML =
              '<div style="margin: 10px;">' +
              '<div style="margin: 10px 0;">' +
              '<aside style="float: left; width: 60px;">' +
              '<img style="margin-right: 5px" src="'+img+'" alt="'+alt+'" />' +
              '</aside>' +
              '<div style="padding-left: 60px"> ' +
              '<p><strong>'+options.date+'</strong>: '+eDate+'</p>'+
              '<p><strong>'+options.table+'</strong>: '+eTable+'</p>' +
              '<p><strong>'+options.etype+'</strong>: '+eType+'</p>' +
              '</div></div>' +
                '<p><strong>'+options.msg+'</strong>:<br>'+eMsg+'</p>' +
              '<p><strong>'+options.sql+'</strong>:</p>' +
              '<textarea style="width: 100%; height: 270px;" readonly>'+eSql+'</textarea> ' +
              '</div>',
            popupOptions = {
              title: options.title,
              body: dHTML,
              buttons: '<input id="e-ok" type="button" class="button-secondary" value="' + options.close + '" onclick="w2popup.close();">',
              width: 500,
              height: 600,
              showMax: true
            };
          w2popup.open(popupOptions);
        });
    });
  });
})(jQuery);