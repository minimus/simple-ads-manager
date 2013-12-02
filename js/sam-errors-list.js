(function($) {
  $(document).ready(function() {
    var cButton = 'Закрыть', dlg = $('#dialog');

    dlg.dialog({
      autoOpen: false,
      height: 550,
      width: 400,
      modal: true,
      buttons: [
        {
          text: options.close,
          click: function() { $(this).dialog("close"); }
        }
      ],
      close: function() {
        $(this).html("");
      }
    });

    $('.more-info').bind('click', function() {
      var
        id = $(this).attr('id').split('-'),
        idn = id[1],
        wOpts = $.parseJSON($.ajax({
        url:ajaxurl,
        data:{
          action:'get_error',
          id: idn
        },
        async:false,
        dataType:'jsonp'
      }).responseText);

      var
        eType = wOpts.data.type,
        eDate = wOpts.data.date,
        eTable = wOpts.data.name,
        eMsg = wOpts.data.msg,
        eSql = wOpts.data.es,
        eResolved = wOpts.data.resolved,
        img = options.imgURL + ((eResolved-0) ? 'ok.png' : 'warning.png'),
        alt = options.alts[eResolved-0],
        dHTML = '<img style="float: left; margin: 5px" src="'+img+'" alt="'+alt+'" />' +
          '<p><strong>'+options.date+'</strong>: '+eDate+'</p>'+
          '<p><strong>'+options.table+'</strong>: '+eTable+'</p>' +
          '<p><strong>'+options.etype+'</strong>:<br/>'+eType+'</p>' +
          '<p><strong>'+options.msg+'</strong>:<br/>'+eMsg+'</p>' +
          '<p><strong>'+options.sql+'</strong>:</p>' +
          '<textarea style="width: 100%; height: 270px;" readonly>'+eSql+'</textarea> ';

      dlg.html(dHTML);
      dlg.dialog('open');
    });
  });
})(jQuery);