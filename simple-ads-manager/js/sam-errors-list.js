(function($) {
  $(document).ready(function() {
    var cButton = 'Закрыть';

    $('#dialog').dialog({
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
        eType = $('#et-'+idn).val(),
        eDate = $('#dt-'+idn).val(),
        eTable = $('#tn-'+idn).val(),
        eMsg = $('#em-'+idn).val(),
        eSql = $('#es-'+idn).val(),
        eResolved = $('#rs-'+idn).val(),
        img = options.imgURL + ((eResolved-0) ? 'ok.png' : 'warning.png'),
        alt = options.alts[eResolved-0],
        dHTML = '<img style="float: left; margin: 5px" src="'+img+'" alt="'+alt+'" />' +
          '<p><strong>'+options.date+'</strong>: '+eDate+'</p>'+
          '<p><strong>'+options.table+'</strong>: '+eTable+'</p>' +
          '<p><strong>'+options.etype+'</strong>:<br/>'+eType+'</p>' +
          '<p><strong>'+options.msg+'</strong>:<br/>'+eMsg+'</p>' +
          '<p><strong>'+options.sql+'</strong>:</p>' +
          '<textarea style="width: 100%; height: 200px;" readonly>'+eSql+'</textarea> ';

      $('#dialog').html(dHTML);
      $('#dialog').dialog('open');
    });
  });
})(jQuery);