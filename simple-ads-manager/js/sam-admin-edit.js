/**
 * @author minimus
 * @copyright 2010
 */
(function ($) {


  $(document).ready(function () {
    var options = $.parseJSON($.ajax({
      url:ajaxurl,
      data:{action:'get_strings'},
      async:false,
      dataType:'jsonp'
    }).responseText);

    var btnUpload = $("#upload-file-button");
    var status = $("#uploading");
    var srcHelp = $("#uploading-help");
    var loadImg = $('#load_img');
    var fileExt = '';

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
      $("#ad_start_date, #ad_end_date").datepicker({
        dateFormat:'yy-mm-dd',
        showButtonPanel:true
      });

      /*var availableCats = options.cats;
      var availableAuthors = options.authors;
      var availableTags = options.tags;
      var availableCustoms = options.customs;*/

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

      // Posts Grid
      var
        postsGrid,
        pgData = options.posts.posts,
        pgOptions = {
          editable:true,
          enableCellNavigation:true,
          asyncEditorLoading:false,
          autoEdit:false
        },
        pgColumns = [],
        pgCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      pgColumns.push(pgCheckboxSelector.getColumnDefinition());
      for (var i = 0; i < options.posts.columns.length; i++) {
        options.posts.columns[i].editor = Slick.Editors.Text;
        pgColumns.push(options.posts.columns[i]);
      }

      postsGrid = new Slick.Grid("#posts-grid", pgData, pgColumns, pgOptions);
      postsGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      postsGrid.registerPlugin(pgCheckboxSelector);

      var postsSelectedItems = $('#view_id').val();
      if (postsSelectedItems != '') {
        var psi = postsSelectedItems.split(','), pgSI = [];
        $.each(pgData, function (i, pd) {
          $.each(psi, function (j, ed) {
            if (ed == pd.id) pgSI.push(i);
          });
        });
        postsGrid.setSelectedRows(pgSI);
      }

      postsGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = postsGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(pgData[row].id);
        });
        $('#view_id').val(items.join(','));
      });

      // Users Grid
      var
        usersGrid,
        ugData = options.users.users,
        ugOptions = {
          editable:true,
          enableCellNavigation:true,
          asyncEditorLoading:false,
          autoEdit:false
        },
        ugColumns = [],
        ugCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      ugColumns.push(ugCheckboxSelector.getColumnDefinition());
      for(i = 0; i < options.users.columns.length; i++) ugColumns.push(options.users.columns[i]);

      usersGrid = new Slick.Grid('#users-grid', ugData, ugColumns, ugOptions);
      usersGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      usersGrid.registerPlugin(ugCheckboxSelector);

      var usersSelectedItems = $('#x_view_users').val();
      if(usersSelectedItems != '') {
        var usi = usersSelectedItems.split(','), ugSI = [];
        $.each(ugData, function(i, ud) {
          $.each(usi, function(j, ed) {
            if(ed == ud.slug) ugSI.push(i);
          });
        });
        usersGrid.setSelectedRows(ugSI);
      }

      usersGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = usersGrid.getSelectedRows();
        $.each(sr, function(i, row) {
          items.push(ugData[row].slug);
        });
        $('#x_view_users').val(items.join(','));
        return false;
      });

      // xPosts Grid
      var
        xpostsGrid,
        xpgColumns = [],
        xpgCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      xpgColumns.push(xpgCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.posts.columns.length; i++) xpgColumns.push(options.posts.columns[i]);

      xpostsGrid = new Slick.Grid("#x-posts-grid", pgData, xpgColumns, pgOptions);
      xpostsGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      xpostsGrid.registerPlugin(xpgCheckboxSelector);

      var xpostsSelectedItems = $('#x_view_id').val();
      if (xpostsSelectedItems != '') {
        var xpsi = xpostsSelectedItems.split(','), xpgSI = [];
        $.each(pgData, function (i, pd) {
          $.each(xpsi, function (j, ed) {
            if (ed == pd.id) xpgSI.push(i);
          });
        });
        xpostsGrid.setSelectedRows(xpgSI);
      }

      xpostsGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = xpostsGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(pgData[row].id);
        });
        $('#x_view_id').val(items.join(','));
      });

      // Categories Grid
      var
        catsGrid,
        cgData = options.cats.cats,
        cgOptions = {
          editable:true,
          enableCellNavigation:true,
          asyncEditorLoading:false,
          autoEdit:false
        },
        cgColumns = [],
        cgCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      cgColumns.push(cgCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.cats.columns.length; i++) {
        options.cats.columns[i].editor = Slick.Editors.Text;
        cgColumns.push(options.cats.columns[i]);
      }

      catsGrid = new Slick.Grid("#cats-grid", cgData, cgColumns, cgOptions);
      catsGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      catsGrid.registerPlugin(cgCheckboxSelector);

      var catsSelectedItems = $('#view_cats').val();
      if (catsSelectedItems != '') {
        var csi = catsSelectedItems.split(','), cgSI = [];
        $.each(cgData, function (i, cd) {
          $.each(csi, function (j, ed) {
            if (ed == cd.slug) cgSI.push(i);
          });
        });
        catsGrid.setSelectedRows(cgSI);
      }

      catsGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = catsGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(cgData[row].slug);
        });
        $('#view_cats').val(items.join(','));
      });

      // xCats Grid
      var
        xcatsGrid,
        xcgColumns = [],
        xcgCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      xcgColumns.push(xcgCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.cats.columns.length; i++) xcgColumns.push(options.cats.columns[i]);

      xcatsGrid = new Slick.Grid("#x-cats-grid", cgData, xcgColumns, cgOptions);
      xcatsGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      xcatsGrid.registerPlugin(xcgCheckboxSelector);

      var xcatsSelectedItems = $('#x_view_cats').val();
      if (xcatsSelectedItems != '') {
        var xcsi = xcatsSelectedItems.split(','), xcgSI = [];
        $.each(cgData, function (i, cd) {
          $.each(xcsi, function (j, ed) {
            if (ed == cd.slug) xcgSI.push(i);
          });
        });
        xcatsGrid.setSelectedRows(xcgSI);
      }

      xcatsGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = xcatsGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(cgData[row].slug);
        });
        $('#x_view_cats').val(items.join(','));
      });

      // Auth Grid
      var
        authGrid,
        agData = options.authors.authors,
        agOptions = {
          editable:true,
          enableCellNavigation:true,
          asyncEditorLoading:false,
          autoEdit:false
        },
        agColumns = [],
        agCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      agColumns.push(agCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.authors.columns.length; i++) {
        options.authors.columns[i].editor = Slick.Editors.Text;
        agColumns.push(options.authors.columns[i]);
      }

      authGrid = new Slick.Grid("#auth-grid", agData, agColumns, agOptions);
      authGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      authGrid.registerPlugin(agCheckboxSelector);

      var authSelectedItems = $('#view_authors').val();
      if (authSelectedItems != '') {
        var asi = authSelectedItems.split(','), agSI = [];
        $.each(agData, function (i, cd) {
          $.each(asi, function (j, ed) {
            if (ed == cd.slug) agSI.push(i);
          });
        });
        authGrid.setSelectedRows(agSI);
      }

      authGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = authGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(agData[row].slug);
        });
        $('#view_authors').val(items.join(','));
      });

      // xauth Grid
      var
        xauthGrid,
        xagColumns = [],
        xagCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      xagColumns.push(xagCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.authors.columns.length; i++) xagColumns.push(options.authors.columns[i]);

      xauthGrid = new Slick.Grid("#x-auth-grid", agData, xagColumns, agOptions);
      xauthGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      xauthGrid.registerPlugin(xagCheckboxSelector);

      var xauthSelectedItems = $('#x_view_authors').val();
      if (xauthSelectedItems != '') {
        var xasi = xauthSelectedItems.split(','), xagSI = [];
        $.each(agData, function (i, cd) {
          $.each(xasi, function (j, ed) {
            if (ed == cd.slug) xagSI.push(i);
          });
        });
        xauthGrid.setSelectedRows(xagSI);
      }

      xauthGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = xauthGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(agData[row].slug);
        });
        $('#x_view_authors').val(items.join(','));
      });

      // Tags Grid
      var
        tagsGrid,
        tgData = options.tags.tags,
        tgOptions = {
          editable:true,
          enableCellNavigation:true,
          asyncEditorLoading:false,
          autoEdit:false
        },
        tgColumns = [],
        tgCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      tgColumns.push(tgCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.tags.columns.length; i++) {
        options.tags.columns[i].editor = Slick.Editors.Text;
        tgColumns.push(options.tags.columns[i]);
      }

      tagsGrid = new Slick.Grid("#tags-grid", tgData, tgColumns, tgOptions);
      tagsGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      tagsGrid.registerPlugin(tgCheckboxSelector);

      var tagsSelectedItems = $('#view_tags').val();
      if (tagsSelectedItems != '') {
        var tsi = tagsSelectedItems.split(','), tgSI = [];
        $.each(tgData, function (i, cd) {
          $.each(tsi, function (j, ed) {
            if (ed == cd.slug) tgSI.push(i);
          });
        });
        tagsGrid.setSelectedRows(tgSI);
      }

      tagsGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = tagsGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(tgData[row].slug);
        });
        $('#view_tags').val(items.join(','));
      });

      // xTags Grid
      var
        xtagsGrid,
        xtgColumns = [],
        xtgCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      xtgColumns.push(xtgCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.tags.columns.length; i++) xtgColumns.push(options.tags.columns[i]);

      xtagsGrid = new Slick.Grid("#x-tags-grid", tgData, xtgColumns, tgOptions);
      xtagsGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      xtagsGrid.registerPlugin(xtgCheckboxSelector);

      var xtagsSelectedItems = $('#x_view_tags').val();
      if (xtagsSelectedItems != '') {
        var xtsi = xtagsSelectedItems.split(','), xtgSI = [];
        $.each(tgData, function (i, cd) {
          $.each(xtsi, function (j, ed) {
            if (ed == cd.slug) xtgSI.push(i);
          });
        });
        xtagsGrid.setSelectedRows(xtgSI);
      }

      xtagsGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = xtagsGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(tgData[row].slug);
        });
        $('#x_view_tags').val(items.join(','));
      });

      // Customs Grid
      var
        custGrid,
        cugData = options.customs.customs,
        cugOptions = {
          editable:true,
          enableCellNavigation:true,
          asyncEditorLoading:false,
          autoEdit:false
        },
        cugColumns = [],
        cugCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      cugColumns.push(cugCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.customs.columns.length; i++) {
        options.customs.columns[i].editor = Slick.Editors.Text;
        cugColumns.push(options.customs.columns[i]);
      }

      custGrid = new Slick.Grid("#cust-grid", cugData, cugColumns, cugOptions);
      custGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      custGrid.registerPlugin(cugCheckboxSelector);

      var custSelectedItems = $('#view_custom').val();
      if (custSelectedItems != '') {
        var cusi = custSelectedItems.split(','), cugSI = [];
        $.each(cugData, function (i, cd) {
          $.each(cusi, function (j, ed) {
            if (ed == cd.slug) cugSI.push(i);
          });
        });
        custGrid.setSelectedRows(cugSI);
      }

      custGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = custGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(cugData[row].slug);
        });
        $('#view_custom').val(items.join(','));
      });

      // xCustoms Grid
      var
        xcustGrid,
        xcugColumns = [],
        xcugCheckboxSelector = new Slick.CheckboxSelectColumn({cssClass:"slick-cell-checkboxsel"});

      xcugColumns.push(xcugCheckboxSelector.getColumnDefinition());
      for (i = 0; i < options.customs.columns.length; i++) xcugColumns.push(options.customs.columns[i]);

      xcustGrid = new Slick.Grid("#x-cust-grid", cugData, xcugColumns, cugOptions);
      xcustGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow:false}));
      xcustGrid.registerPlugin(xcugCheckboxSelector);

      var xcustSelectedItems = $('#x_view_custom').val();
      if (xcustSelectedItems != '') {
        var xcusi = xcustSelectedItems.split(','), xcugSI = [];
        $.each(cugData, function (i, cd) {
          $.each(xcusi, function (j, ed) {
            if (ed == cd.slug) xcugSI.push(i);
          });
        });
        xcustGrid.setSelectedRows(xcugSI);
      }

      xcustGrid.onSelectedRowsChanged.subscribe(function (e) {
        var items = [], sr = xcustGrid.getSelectedRows();
        $.each(sr, function (i, row) {
          items.push(cugData[row].slug);
        });
        $('#x_view_custom').val(items.join(','));
      });


      $('#tabs').tabs();

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
              $("#rc-vt2").show('blind', {direction:'vertical'}, 500);
              postsGrid.invalidate();
            }
        }
      });

      $("input:radio[name=ad_users]").click(function() {
        var uval = $('input:radio[name=ad_users]:checked').val();
        if(uval == '0') {
          if($('#custom-users').is(':visible')) $('#custom-users').hide('blind', {direction:'vertical'}, 500);
        }
        else {
          if($('#custom-users').is(':hidden')) $('#custom-users').show('blind', {direction:'vertical'}, 500);
        }
      });

      $("#ad_users_reg").click(function() {
        if($('#ad_users_reg').is(':checked')) $('#x-reg-users').show('blind', {direction:'vertical'}, 500);
        else $('#x-reg-users').hide('blind', {direction:'vertical'}, 500);
      });

      $('#x_ad_users').click(function() {
        if($('#x_ad_users').is(':checked')) $('#x-view-users').show('blind', {direction:'vertical'}, 500);
        else $('#x-view-users').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_swf').click(function() {
        if($('#ad_swf').is(':checked')) $('#swf-params').show('blind', {direction:'vertical'}, 500);
        else $('#swf-params').hide('blind', {direction:'vertical'}, 500);
      });

      $('#x_id').click(function () {
        if ($('#x_id').is(':checked')) {
          $('#rc-xid').show('blind', {direction:'vertical'}, 500);
          xpostsGrid.invalidate();
        }
        else $('#rc-xid').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_cats').click(function () {
        if ($('#ad_cats').is(':checked')) {
          $('#rc-ac').show('blind', {direction:'vertical'}, 500);
          $('#acw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-ac').hide('blind', {direction:'vertical'}, 500);
          $('#acw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_cats').click(function () {
        if ($('#x_cats').is(':checked')) $('#rc-xc').show('blind', {direction:'vertical'}, 500);
        else $('#rc-xc').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_authors').click(function () {
        if ($('#ad_authors').is(':checked')) {
          $('#rc-au').show('blind', {direction:'vertical'}, 500);
          $('#aaw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-au').hide('blind', {direction:'vertical'}, 500);
          $('#aaw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_authors').click(function () {
        if ($('#x_authors').is(':checked')) $('#rc-xa').show('blind', {direction:'vertical'}, 500);
        else $('#rc-xa').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_tags').click(function () {
        if ($('#ad_tags').is(':checked')) {
          $('#rc-at').show('blind', {direction:'vertical'}, 500);
          $('#atw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-at').hide('blind', {direction:'vertical'}, 500);
          $('#atw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_tags').click(function () {
        if ($('#x_tags').is(':checked')) $('#rc-xt').show('blind', {direction:'vertical'}, 500);
        else $('#rc-xt').hide('blind', {direction:'vertical'}, 500);
      });

      $('#ad_custom').click(function () {
        if ($('#ad_custom').is(':checked')) {
          $('#rc-cu').show('blind', {direction:'vertical'}, 500);
          $('#cuw').show('blind', {direction:'vertical'}, 500);
        }
        else {
          $('#rc-cu').hide('blind', {direction:'vertical'}, 500);
          $('#cuw').hide('blind', {direction:'vertical'}, 500);
        }
      });

      $('#x_custom').click(function () {
        if ($('#x_custom').is(':checked')) $('#rc-xu').show('blind', {direction:'vertical'}, 500);
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