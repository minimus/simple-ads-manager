/**
 * @author minimus
 * @copyright 2010
 */
(function ($) {
  $(document).ready(function () {
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
          if ($('#editor_mode').val() == 'item') $("#ad_img").val(options.url + file);
          if ($('#editor_mode').val() == 'place') $("#patch_img").val(options.url + file);
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

    if ($('#editor_mode').val() == 'item') {
      sPointer = samPointer.ads;
      sPointer.pointer = 'ads';

      $("#ad_start_date, #ad_end_date").datepicker({
        dateFormat:'yy-mm-dd',
        showButtonPanel:true
      });

      // Advertiser ComboGrid
      $('#adv_nick').combogrid({
        url: ajaxurl+'?action=get_combo_data',
        datatype: "json",
        munit: 'px',
        alternate: true,
        colModel: options.users.colModel,
        select: function(event, ui) {
          $('#adv_nick').val(ui.item.slug);
          $('#adv_name').val(ui.item.title);
          $('#adv_mail').val(ui.item.email);
          return false;
        }
      });

      function buildGrid(name, grig, vn, vi, field, gc, gr) {
        //grig = $('#' + name);
        //vi = $('#' + vn);
        var iVal = vi.val();
        grig.w2grid({
          name: name,
          show: {selectColumn: true},
          multiSelect: true,
          columns: gc,
          records: gr,
          onSelect: function(event) {
            event.onComplete = function() {
              var out = '', recs = this.getSelection(), data;
              for(var i = 0; i < recs.length; i++) {
                var rec = this.get(recs[i]);
                data = (field == 'id') ? rec.id : rec.slug;
                out += (i == recs.length - 1) ? data : (data + ',');
              }
              vi.val(out);
            }
          },
          onUnselect: function(event) {
            event.onComplete = function() {
              var out = '', recs = this.getSelection(), data;
              for(var i = 0; i < recs.length; i++) {
                var rec = this.get(recs[i]);
                data = (field == 'id') ? rec.id : rec.slug;
                out += (i == recs.length - 1) ? data : (data + ',');
              }
              vi.val(out);
            }
          }
        });

        if(null != iVal && '' != iVal) {
          var arr = iVal.split(',');

          $.each(arr, function(i, val) {
            $.each(gr, function(index, value) {
              var iData = (field == 'id') ? value.id : value.slug;
              if(iData == val) {
                w2ui[name].select(value.recid);
                return false;
              }
              else return true;
            });
          });
        }
      }

      // Custom Taxonomies Terms Grid
      var cttGrid = $('#ctt-grid'), cttIn = $('#view-custom-tax-terms');
      buildGrid('ctt-grid', cttGrid, 'view-custom-tax-terms', cttIn, 'slug', options.custom_taxes.columns, options.custom_taxes.taxes);

      // Posts Grid
      var postsGrid = $('#posts-grid'), postsIn = $('#view_id');
      buildGrid('posts-grid', postsGrid, 'view_id', postsIn, 'id', options.posts.columns, options.posts.posts);

      // Users Grid
      var usersGrid = $('#users-grid'), usersIn = $('#x_view_users');
      buildGrid('users-grid', usersGrid, 'x_view_users', usersIn, 'slug', options.users.columns, options.users.users);

      // xPosts Grid
      var xpostsGrid = $('#x-posts-grid'), xpostsIn = $('#x_view_id');
      buildGrid('x-posts-grid', xpostsGrid, 'x_view_id', xpostsIn, 'id', options.posts.columns, options.posts.posts);

      // Categories Grid
      var catsGrid = $('#cats-grid'), catsIn = $('#view_cats');
      buildGrid('cats-grid', catsGrid, 'view_cats', catsIn, 'slug', options.cats.columns, options.cats.cats);

      // xCats Grid
      var xcatsGrid = $('#x-cats-grid'), xcatsIn = $('#x_view_cats');
      buildGrid('x-cats-grid', xcatsGrid, 'x_view_cats', xcatsIn, 'slug', options.cats.columns, options.cats.cats);

      // Auth Grid
      var authGrid = $('#auth-grid'), authIn = $('#view_authors');
      buildGrid('auth-grid', authGrid, 'view_authors', authIn, 'slug', options.authors.columns, options.authors.authors);

      // xauth Grid
      var xauthGrid = $('#x-auth-grid'), xauthIn = $('#x_view_authors');
      buildGrid('x-auth-grid', xauthGrid, 'x_view_authors', xauthIn, 'slug', options.authors.columns, options.authors.authors);

      // Tags Grid
      var tagsGrid = $('#tags-grid'), tagsIn = $('#view_tags');
      buildGrid('tags-grid', tagsGrid, 'view_tags', tagsIn, 'slug', options.tags.columns, options.tags.tags);

      // xTags Grid
      var xtagsGrid = $('#x-tags-grid'), xtagsIn = $('#x_view_tags');
      buildGrid('x-tags-grid', xtagsGrid, 'x_view_tags', xtagsIn, 'slug', options.tags.columns, options.tags.tags);

      // Customs Grid
      var custGrid = $('#cust-grid'), custIn = $('#view_custom');
      buildGrid('cust-grid', custGrid, 'view_custom', custIn, 'slug', options.customs.columns, options.customs.customs);

      // xCustoms Grid
      var xcustGrid = $('#x-cust-grid'), xcustIn = $('#x_view_custom');
      buildGrid('x-cust-grid', xcustGrid, 'x_view_custom', xcustIn, 'slug', options.customs.columns, options.customs.customs);

      $('#tabs').tabs({
        activate: function( event, ui ) {
          var el = ui.newPanel[0].id;
          if(el == 'tabs-1') {
            postsGrid.w2render('posts-grid');
          }
          if(el == 'tabs-2') {
            if($('#rc-ctt').is(':visible')) cttGrid.w2render('ctt-grid');
            if($('#rc-xid').is(':visible')) xpostsGrid.w2render('x-posts-grid');
            if($('#x-view-users').is(':visible')) usersGrid.w2render('users-grid');
            if($('#rc-ac').is(':visible')) catsGrid.w2render('cats-grid');
            if($('#rc-xc').is(':visible')) xcatsGrid.w2render('x-cats-grid');
            if($('#rc-au').is(':visible')) authGrid.w2render('auth-grid');
            if($('#rc-xa').is(':visible')) xauthGrid.w2render('x-auth-grid');
            if($('#rc-at').is(':visible')) tagsGrid.w2render('tags-grid');
            if($('#rc-xt').is(':visible')) xtagsGrid.w2render('x-tags-grid');
            if($('#rc-cu').is(':visible')) custGrid.w2render('cust-grid');
            if($('#rc-xu').is(':visible')) xcustGrid.w2render('x-cust-grid');
          }
        }
      });

      $("#add-file-button").click(function () {
        var curFile = options.url + $("select#files_list option:selected").val();
        $("#ad_img").val(curFile);
        return false;
      });

      $('#code_mode_false').click(function () {
        $("#rc-cmf").show('blind', {direction:'vertical'}, 500);
        $("#rc-cmt").hide('blind', {direction:'vertical'}, 500);
      });

      $('#code_mode_true').click(function () {
        $("#rc-cmf").hide('blind', {direction:'vertical'}, 500);
        $("#rc-cmt").show('blind', {direction:'vertical'}, 500);
      });

      $("input:radio[name=view_type]").click(function () {
        var cval = $('input:radio[name=view_type]:checked').val();
        switch (cval) {
          case '0':
            if ($('#rc-vt0').is(':hidden')) $("#rc-vt0").show('blind', {direction:'vertical'}, 500);
            if ($('#rc-vt2').is(':visible')) $("#rc-vt2").hide('blind', {direction:'vertical'}, 500);
            break;
          case '1':
            if ($('#rc-vt0').is(':visible')) $("#rc-vt0").hide('blind', {direction:'vertical'}, 500);
            if ($('#rc-vt2').is(':visible')) $("#rc-vt2").hide('blind', {direction:'vertical'}, 500);
            break;
          case '2':
            if ($('#rc-vt0').is(':visible')) $("#rc-vt0").hide('blind', {direction:'vertical'}, 500);
            if ($('#rc-vt2').is(':hidden')) {
              $("#rc-vt2").show('blind', {direction:'vertical'}, 500, function() {
                postsGrid.w2render('posts-grid');
              });
            }
        }
      });

      $("input:radio[name=ad_users]").click(function() {
        var uval = $('input:radio[name=ad_users]:checked').val();
        if(uval == '0') {
          if($('#custom-users').is(':visible')) $('#custom-users').hide('blind', {direction:'vertical'}, 500);
        }
        else {
          if($('#custom-users').is(':hidden'))
            $('#custom-users').show('blind', {direction:'vertical'}, 500, function() {
              if($('#x-view-users').is(':visible')) usersGrid.w2render('users-grid');
            });
        }
      });

      $("#ad_users_reg").click(function() {
        if($('#ad_users_reg').is(':checked'))
          $('#x-reg-users').show('blind', {direction:'vertical'}, 500, function() {
            if($('#x-view-users').is(':visible')) usersGrid.w2render('users-grid');
          });
        else $('#x-reg-users').hide('blind', {direction:'vertical'}, 500);
      });

      $('#x_ad_users').click(function() {
        if($('#x_ad_users').is(':checked'))
          $('#x-view-users').show('blind', {direction:'vertical'}, 500, function() {
            usersGrid.w2render('users-grid');
          });
        else $('#x-view-users').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_swf').click(function() {
        if($('#ad_swf').is(':checked')) $('#swf-params').show('blind', {direction:'vertical'}, 500);
        else $('#swf-params').hide('blind', {direction:'vertical'}, 500);
      });

      $('#x_id').click(function () {
        if ($('#x_id').is(':checked')) {
          $('#rc-xid').show('blind', {direction:'vertical'}, 500, function() {
            xpostsGrid.w2render('x-posts-grid');
          });
        }
        else $('#rc-xid').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_cats').click(function () {
        if ($('#ad_cats').is(':checked')) {
          $('#rc-ac').show('blind', {direction:'vertical'}, 500, function() {
            catsGrid.w2render('cats-grid');
          });
          $('#acw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-ac').hide('blind', {direction:'vertical'}, 500);
          $('#acw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_cats').click(function () {
        if ($('#x_cats').is(':checked'))
          $('#rc-xc').show('blind', {direction:'vertical'}, 500, function() {
            xcatsGrid.w2render('x-cats-grid');
          });
        else $('#rc-xc').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_custom_tax_terms').click(function() {
        if($('#ad_custom_tax_terms').is(':checked'))
          $('#rc-ctt').show('blind', {direction: 'vertical'}, 500, function() {
            cttGrid.w2render('ctt-grid');
          });
        else $('#rc-ctt').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_authors').click(function () {
        if ($('#ad_authors').is(':checked')) {
          $('#rc-au').show('blind', {direction:'vertical'}, 500, function() {
            authGrid.w2render('auth-grid');
          });
          $('#aaw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-au').hide('blind', {direction:'vertical'}, 500);
          $('#aaw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_authors').click(function () {
        if ($('#x_authors').is(':checked'))
          $('#rc-xa').show('blind', {direction:'vertical'}, 500, function() {
            xauthGrid.w2render('x-auth-grid');
          });
        else $('#rc-xa').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_tags').click(function () {
        if ($('#ad_tags').is(':checked')) {
          $('#rc-at').show('blind', {direction:'vertical'}, 500, function() {
            tagsGrid.w2render('tags-grid');
          });
          $('#atw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-at').hide('blind', {direction:'vertical'}, 500);
          $('#atw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_tags').click(function () {
        if ($('#x_tags').is(':checked'))
          $('#rc-xt').show('blind', {direction:'vertical'}, 500, function() {
            xtagsGrid.w2render('x-tags-grid');
          });
        else $('#rc-xt').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_custom').click(function () {
        if ($('#ad_custom').is(':checked')) {
          $('#rc-cu').show('blind', {direction:'vertical'}, 500, function() {
            custGrid.w2render('cust-grid');
          });
          $('#cuw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-cu').hide('blind', {direction:'vertical'}, 500);
          $('#cuw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_custom').click(function () {
        if ($('#x_custom').is(':checked'))
          $('#rc-xu').show('blind', {direction:'vertical'}, 500, function() {
            xcustGrid.w2render('x-cust-grid');
          });
        else $('#rc-xu').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_schedule').click(function () {
        if ($('#ad_schedule').is(':checked')) $('#rc-sc').show('blind', {direction:'vertical'}, 500);
        else $('#rc-sc').hide('blind', {direction:'vertical'}, 500);
      });

      $('#limit_hits').click(function () {
        if ($('#limit_hits').is(':checked')) $('#rc-hl').show('blind', {direction:'vertical'}, 500);
        else $('#rc-hl').hide('blind', {direction:'vertical'}, 500);
      });

      $('#limit_clicks').click(function () {
        if ($('#limit_clicks').is(':checked')) $('#rc-cl').show('blind', {direction:'vertical'}, 500);
        else $('#rc-cl').hide('blind', {direction:'vertical'}, 500);
      });
    }

    if ($('#editor_mode').val() == 'place') {
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
    }

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

    $('#is_singular').click(function () {
      if ($('#is_singular').is(':checked'))
        $('#is_single, #is_page, #is_attachment, #is_posttype').attr('checked', true);
    });

    $('#is_single, #is_page, #is_attachment, #is_posttype').click(function () {
      if ($('#is_singular').is(':checked') &&
        (!$('#is_single').is(':checked') ||
          !$('#is_page').is(':checked') ||
          !$('#is_attachment').is(':checked') ||
          !$('#is_posttype').is(':checked') )) {
        $('#is_singular').attr('checked', false);
      }
      else {
        if (!$('#is_singular').is(':checked') &&
          $('#is_single').is(':checked') &&
          $('#is_posttype').is(':checked') &&
          $('#is_page').is(':checked') &&
          $('#is_attachment').is(':checked'))
          $('#is_singular').attr('checked', true);
      }
    });

    $('#is_archive').click(function () {
      if ($('#is_archive').is(':checked'))
        $('#is_tax, #is_category, #is_tag, #is_author, #is_date, #is_posttype_archive').attr('checked', true);
    });

    $('#is_tax, #is_category, #is_tag, #is_author, #is_date, #is_posttype_archive').click(function () {
      if ($('#is_archive').is(':checked') &&
        (!$('#is_tax').is(':checked') ||
          !$('#is_category').is(':checked') ||
          !$('#is_posttype_archive').is(':checked') ||
          !$('#is_tag').is(':checked') ||
          !$('#is_author').is(':checked') ||
          !$('#is_date').is(':checked'))) {
        $('#is_archive').attr('checked', false);
      }
      else {
        if (!$('#is_archive').is(':checked') &&
          $('#is_tax').is(':checked') &&
          $('#is_category').is(':checked') &&
          $('#is_posttype_archive').is(':checked') &&
          $('#is_tag').is(':checked') &&
          $('#is_author').is(':checked') &&
          $('#is_date').is(':checked')) {
          $('#is_archive').attr('checked', true);
        }
      }
    });

    return false;
  });
})(jQuery);