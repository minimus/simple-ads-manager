/**
 * @author minimus
 * @copyright 2010
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

    if (em == 'item') {
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

      //
      var xcttGrid = $('#x-ctt-grid'), xcttIn = $('#x-view-custom-tax-terms');
      buildGrid('x-ctt-grid', xcttGrid, 'x-view-custom-tax-terms', xcttIn, 'slug', options.custom_taxes.columns, options.custom_taxes.taxes);

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
            if($('#rc-xct').is(':visible')) xcttGrid.w2render('x-ctt-grid');
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

      var
        rcvt0 = $('#rc-vt0'),
        rcvt2 = $('#rc-vt2'),
        xId = $('#x_id'),
        rcxid = $('#rc-xid');

      if(2 == $('input:radio[name=view_type]:checked').val()) {
        if(xId.is(':checked')) {
          xId.attr('checked', false);
          rcxid.hide('blind', {direction:'vertical'}, 500);
        }
        xId.attr('disabled', true);
      }

      $("input:radio[name=view_type]").click(function () {
        var cval = $('input:radio[name=view_type]:checked').val();
        switch (cval) {
          case '0':
            if (rcvt0.is(':hidden')) rcvt0.show('blind', {direction:'vertical'}, 500);
            if (rcvt2.is(':visible')) rcvt2.hide('blind', {direction:'vertical'}, 500);
            xId.attr('disabled', false);
            break;
          case '1':
            if (rcvt0.is(':visible')) rcvt0.hide('blind', {direction:'vertical'}, 500);
            if (rcvt2.is(':visible')) rcvt2.hide('blind', {direction:'vertical'}, 500);
            xId.attr('disabled', false);
            break;
          case '2':
            if (rcvt0.is(':visible')) rcvt0.hide('blind', {direction:'vertical'}, 500);
            if (rcvt2.is(':hidden')) {
              rcvt2.show('blind', {direction:'vertical'}, 500, function() {
                postsGrid.w2render('posts-grid');
              });
              if(xId.is(':checked')) {
                xId.attr('checked', false);
                rcxid.hide('blind', {direction:'vertical'}, 500);
              }
            }
            xId.attr('disabled', true);
            break;
        }
      });

      xId.click(function () {
        if (xId.is(':checked')) {
          if(2 == $('input:radio[name=view_type]:checked').val()) {
            xId.attr('checked', false);
          }
          else
            rcxid.show('blind', {direction:'vertical'}, 500, function() {
              xpostsGrid.w2render('x-posts-grid');
            });
        }
        else rcxid.hide('blind', {direction:'vertical'}, 500);
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

      var
        adCats = $('#ad_cats'),
        rcac = $('#rc-ac'),
        acw = $('#acw'),
        xCats = $('#x_cats'),
        rcxc = $('#rc-xc');

      if(adCats.is(':checked') && xCats.is(':checked')) {
        xCats.attr('checked', false);
        rcxc.hide('blind', {direction:'vertical'}, 500);
      }

      adCats.click(function () {
        if (adCats.is(':checked')) {
          rcac.show('blind', {direction:'vertical'}, 500, function() {
            catsGrid.w2render('cats-grid');
          });
          acw.show('blind', {direction:'vertical'}, 500);
          if(xCats.is(':checked')) {
            xCats.attr('checked', false);
            rcxc.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else {
          rcac.hide('blind', {direction:'vertical'}, 500);
          acw.hide('blind', {direction:'vertical'}, 500);
        }
      });

      xCats.click(function () {
        if (xCats.is(':checked')) {
          rcxc.show('blind', {direction:'vertical'}, 500, function() {
            xcatsGrid.w2render('x-cats-grid');
          });
          if(adCats.is(':checked')) {
            adCats.attr('checked', false);
            rcac.hide('blind', {direction:'vertical'}, 500);
            acw.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else rcxc.hide('blind', {direction:'vertical'}, 500);
      });

      var
        actt = $('#ad_custom_tax_terms'),
        rcctt = $('#rc-ctt'),
        cttw = $('#cttw'),
        xacct = $('#x_ad_custom_tax_terms'),
        rcxct = $('#rc-xct');

      if(actt.is(':checked') && xacct.is(':checked')) {
        xacct.attr('checked', false);
        rcxct.hide('blind', {direction:'vertical'}, 500);
      }

      actt.click(function() {
        if(actt.is(':checked')) {
          rcctt.show('blind', {direction: 'vertical'}, 500, function() {
            cttGrid.w2render('ctt-grid');
          });
          cttw.show('blind', {direction:'vertical'}, 500);
          if(xacct.is(':checked')) {
            xacct.attr('checked', false);
            rcxct.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else {
          rcctt.hide('blind', {direction:'vertical'}, 500);
          cttw.hide('blind', {direction:'vertical'}, 500);
        }
      });

      xacct.click(function() {
        if(xacct.is(':checked')) {
          rcxct.show('blind', {direction: 'vertical'}, 500, function() {
            xcttGrid.w2render('x-ctt-grid');
          });
          if(actt.is(':checked')) {
            actt.attr('checked', false);
            rcctt.hide('blind', {direction:'vertical'}, 500);
            cttw.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else rcxct.hide('blind', {direction: 'vertical'}, 500);
      });

      var
        adAuth = $('#ad_authors'),
        rcau = $('#rc-au'),
        aaw = $('#aaw'),
        xAuth = $('#x_authors'),
        rcxa = $('#rc-xa');

      if(adAuth.is(':checked') && xAuth.is(':checked')) {
        xAuth.attr('checked', false);
        rcxa.hide('blind', {direction:'vertical'}, 500);
      }

      adAuth.click(function () {
        if (adAuth.is(':checked')) {
          rcau.show('blind', {direction:'vertical'}, 500, function() {
            authGrid.w2render('auth-grid');
          });
          aaw.show('blind', {direction:'vertical'}, 500);
          if(xAuth.is(':checked')) {
            xAuth.attr('checked', false);
            rcxa.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else {
          rcau.hide('blind', {direction:'vertical'}, 500);
          aaw.hide('blind', {direction:'vertical'}, 500);
        }
      });

      xAuth.click(function () {
        if (xAuth.is(':checked')) {
          rcxa.show('blind', {direction:'vertical'}, 500, function() {
            xauthGrid.w2render('x-auth-grid');
          });
          if(adAuth.is(':checked')) {
            adAuth.attr('checked', false);
            rcau.hide('blind', {direction:'vertical'}, 500);
            aaw.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else rcxa.hide('blind', {direction:'vertical'}, 500);
      });

      var
        adTags = $('#ad_tags'),
        rcat = $('#rc-at'),
        atw = $('#atw'),
        xTags = $('#x_tags'),
        rcxt = $('#rc-xt');

      if(adTags.is(':checked') && xTags.is(':checked')) {
        xTags.attr('checked', false);
        rcxt.hide('blind', {direction:'vertical'}, 500);
      }

      adTags.click(function () {
        if (adTags.is(':checked')) {
          rcat.show('blind', {direction:'vertical'}, 500, function() {
            tagsGrid.w2render('tags-grid');
          });
          atw.show('blind', {direction:'vertical'}, 500);
          if(xTags.is(':checked')) {
            xTags.attr('checked', false);
            rcxt.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else {
          rcat.hide('blind', {direction:'vertical'}, 500);
          atw.hide('blind', {direction:'vertical'}, 500);
        }
      });

      xTags.click(function () {
        if (xTags.is(':checked')) {
          rcxt.show('blind', {direction:'vertical'}, 500, function() {
            xtagsGrid.w2render('x-tags-grid');
          });
          if(adTags.is(':checked')) {
            adTags.attr('checked', false);
            rcat.hide('blind', {direction:'vertical'}, 500);
            atw.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else rcxt.hide('blind', {direction:'vertical'}, 500);
      });

      var
        adCust = $('#ad_custom'),
        rccu = $('#rc-cu'),
        cuw = $('#cuw'),
        xCust = $('#x_custom'),
        rcxu = $('#rc-xu');

      if(adCust.is(':checked') && xCust.is(':checked')) {
        xCust.attr('checked', false);
        rcxu.hide('blind', {direction:'vertical'}, 500);
      }

      adCust.click(function () {
        if (adCust.is(':checked')) {
          rccu.show('blind', {direction:'vertical'}, 500, function() {
            custGrid.w2render('cust-grid');
          });
          cuw.show('blind', {direction:'vertical'}, 500);
          if(xCust.is(':checked')) {
            xCust.attr('checked', false);
            rcxu.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else {
          rccu.hide('blind', {direction:'vertical'}, 500);
          cuw.hide('blind', {direction:'vertical'}, 500);
        }
      });

      xCust.click(function () {
        if (xCust.is(':checked')) {
          rcxu.show('blind', {direction:'vertical'}, 500, function() {
            xcustGrid.w2render('x-cust-grid');
          });
          if(adCust.is(':checked')) {
            adCust.attr('checked', false);
            rccu.hide('blind', {direction:'vertical'}, 500);
            cuw.hide('blind', {direction:'vertical'}, 500);
          }
        }
        else rcxu.hide('blind', {direction:'vertical'}, 500);
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

    if (em == 'place') {
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