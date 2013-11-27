/**
 * Created by minimus on 17.11.13.
 */

(function ($) {
  $(document).ready(function () {
    var em = $('#editor_mode').val();

    $("#title").tooltip({
      track: true
    });

    var options = $.parseJSON($.ajax({
      url:ajaxurl,
      data:{action:'get_strings'},
      async:false,
      dataType:'jsonp'
    }).responseText);

    var
      btnUpload = $("#upload-file-button"),
      status = $("#uploading"),
      srcHelp = $("#uploading-help"),
      loadImg = $('#load_img'),
      sPointer,
      fileExt = '';

    var fu = new AjaxUpload(btnUpload, {
      action:ajaxurl,
      name:'uploadfile',
      data:{
        action:'upload_ad_image'
      },
      onSubmit:function (file, ext) {
        if (!(ext && /^(jpg|png|jpeg|gif|swf)$/.test(ext))) {
          status.text(options.status);
          return false;
        }
        loadImg.show();
        status.text(options.uploading);
      },
      onComplete:function (file, response) {
        status.text('');
        loadImg.hide();
        $('<div id="files"></div>').appendTo(srcHelp);
        if (response == "success") {
          $("#files").text(options.file + ' ' + file + ' ' + options.uploaded)
            .addClass('updated')
            .delay(3000)
            .fadeOut(1000, function () {
              $(this).remove();
            });
          if (em == 'item') $("#ad_img").val(options.url + file);
          else if (em == 'place') $("#patch_img").val(options.url + file);
        }
        else {
          $('#files').text(file + ' ' + response)
            .addClass('error')
            .delay(3000)
            .fadeOut(1000, function () {
              $(this).remove();
            });
        }
      }
    });

    sPointer = samPointer.places;
    sPointer.pointer = 'places';

    $("#add-file-button").click(function () {
      var curFile = options.url + $("select#files_list option:selected").val();
      $("#patch_img").val(curFile);
      return false;
    });

    $('#patch_source_image').click(function () {
      if ($('#rc-psi').is(':hidden')) $('#rc-psi').show('blind', {direction:'vertical'}, 500);
      if ($('#rc-psc').is(':visible')) $('#rc-psc').hide('blind', {direction:'vertical'}, 500);
      if ($('#rc-psd').is(':visible')) $('#rc-psd').hide('blind', {direction:'vertical'}, 500);
    });

    $('#patch_source_code').click(function () {
      if ($('#rc-psi').is(':visible')) $('#rc-psi').hide('blind', {direction:'vertical'}, 500);
      if ($('#rc-psc').is(':hidden')) $('#rc-psc').show('blind', {direction:'vertical'}, 500);
      if ($('#rc-psd').is(':visible')) $('#rc-psd').hide('blind', {direction:'vertical'}, 500);
    });

    $('#patch_source_dfp').click(function () {
      if ($('#rc-psi').is(':visible')) $('#rc-psi').hide('blind', {direction:'vertical'}, 500);
      if ($('#rc-psc').is(':visible')) $('#rc-psc').hide('blind', {direction:'vertical'}, 500);
      if ($('#rc-psd').is(':hidden')) $('#rc-psd').show('blind', {direction:'vertical'}, 500);
    });

    if(sPointer.enabled || '' == $('#title').val()) {
      $('#title').pointer({
        content: '<h3>' + sPointer.title + '</h3><p>' + sPointer.content + '</p>',
        position: 'top',
        close: function() {
          $.ajax({
            url: ajaxurl,
            data: {
              action: 'close_pointer',
              pointer: sPointer.pointer
            },
            async: true
          });
        }
      }).pointer('open');
    }

    return false;
  });
})(jQuery);