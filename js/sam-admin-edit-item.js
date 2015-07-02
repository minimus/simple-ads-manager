/**
 * Created by minimus on 17.11.13.
 */
var sam = sam || {};
(function ($) {
  var media, mediaTexts = samEditorOptions.media, options = samEditorOptions.strings, iAction = samEditorOptions.action;

  sam.media = media = {
    buttonId: '#banner-media',
    adUrl: '#ad_img',
    adImgId: '#ad_img_id',
    adName: '#title',
    adDesc: '#item_description',
    adAlt: '#ad_alt',
    adTarget: '#ad_target',
    fbBtnId: '#fallback-code',
    adFallback: '#ad_swf_fallback',

    init: function() {
      $(this.buttonId).on( 'click', this.openMediaDialog );
      $(this.fbBtnId).on( 'click', this.openMediaDialog );
    },

    openMediaDialog: function( e ) {
      e.preventDefault();
      var btnId = '#' + e.currentTarget.id;

      if ( this._frame ) {
        this._frame.open();
        return;
      }

      //var Attachment = wp.media.model.Attachment;

      this._frame = media.frame = wp.media({
        title: mediaTexts.title,
        button: {
          text: mediaTexts.button
        },
        multiple: false,
        library: {
          type: 'image, *flash*'
        }/*,
         selection: [ Attachment.get( $(this.adImgId).val() ) ]*/
      });

      this._frame.on('ready', function() {
        //
      });

      this._frame.state( 'library' ).on('select', function() {
        var attachment = this.get( 'selection' ).single();
        media.handleMediaAttachment( attachment, btnId );
      });

      this._frame.open();
    },

    handleMediaAttachment: function(a, id) {
      var attachment = a.toJSON();
      if(id == this.buttonId) {
        $(this.adUrl).val(attachment.url);
        $(this.adImgId).val(attachment.id);
        if('' == $(this.adName).val() && '' != attachment.title) $(this.adName).val(attachment.title);
        if('' == $(this.adDesc).val() && '' != attachment.caption) $(this.adDesc).val(attachment.caption);
        if('' == $(this.adAlt).val() && '' != attachment.alt) $(this.adAlt).val(attachment.alt);
      }
      else if(id == this.fbBtnId) {
        var
          target = $(this.adTarget).val(),
          anchor = (target != '') ? '<a href="' + target + '">' : '',
          image = '<img src="' + attachment.url + '">',
          anchorE = (target != '') ? '</a>' : '',
          code = anchor + image + anchorE;
        $(this.adFallback).val(code);
      }
    }
  };

  $(document).ready(function () {
    var em = $('#editor_mode').val(), fu, title = $('#title');

    var
      rcvt0 = $('#rc-vt0'), rcvt2 = $('#rc-vt2'), xId = $('#x_id'), rcxid = $('#rc-xid'),
      adCats = $('#ad_cats'), rcac = $('#rc-ac'), acw = $('#acw'), xCats = $('#x_cats'), rcxc = $('#rc-xc'),
      actt = $('#ad_custom_tax_terms'), rcctt = $('#rc-ctt'), cttw = $('#cttw'), xacct = $('#x_ad_custom_tax_terms'), rcxct = $('#rc-xct'),
      adAuth = $('#ad_authors'), rcau = $('#rc-au'), aaw = $('#aaw'), xAuth = $('#x_authors'), rcxa = $('#rc-xa'),
      adTags = $('#ad_tags'), rcat = $('#rc-at'), atw = $('#atw'), xTags = $('#x_tags'), rcxt = $('#rc-xt'),
      adCust = $('#ad_custom'), rccu = $('#rc-cu'), cuw = $('#cuw'), xCust = $('#x_custom'), rcxu = $('#rc-xu'),
      xViewUsers = $('#x-view-users'), custUsers = $('#custom-users'), rccmf = $("#rc-cmf"), rccmt = $("#rc-cmt"),

      adUsersReg = $("#ad_users_reg"), xRegUsers = $('#x-reg-users'), xAdUsers = $('#x_ad_users'),

      btnUpload = $("#upload-file-button"), status = $("#uploading"), srcHelp = $("#uploading-help"),
      loadImg = $('#load_img'), sPointer,

      cttGrid = $('#ctt-grid'), cttIn = $('#view_custom_tax_terms'),
      xcttGrid = $('#x-ctt-grid'), xcttIn = $('#x_view_custom_tax_terms'),
      postsGrid = $('#posts-grid'), postsIn = $('#view_id'),
      usersGrid = $('#users-grid'), usersIn = $('#x_view_users'),
      xpostsGrid = $('#x-posts-grid'), xpostsIn = $('#x_view_id'),
      catsGrid = $('#cats-grid'), catsIn = $('#view_cats'),
      xcatsGrid = $('#x-cats-grid'), xcatsIn = $('#x_view_cats'),
      authGrid = $('#auth-grid'), authIn = $('#view_authors'),
      xauthGrid = $('#x-auth-grid'), xauthIn = $('#x_view_authors'),
      tagsGrid = $('#tags-grid'), tagsIn = $('#view_tags'),
      xtagsGrid = $('#x-tags-grid'), xtagsIn = $('#x_view_tags'),
      custGrid = $('#cust-grid'), custIn = $('#view_custom'),
      xcustGrid = $('#x-cust-grid'), xcustIn = $('#x_view_custom');

    var
      samAjaxUrl = samEditorOptions.samAjaxUrl,
      samStatsUrl = samEditorOptions.samStatsUrl,
      models = samEditorOptions.models,
      searches = samEditorOptions.searches,
      gData = samEditorOptions.data,
      samStrs = samEditorOptions.strings,
      sPost = encodeURI(samStrs.posts), sPage = encodeURI(samStrs.page);

    var stats, itemId = $('#item_id').val(), sMonth = 0;
    var plot, plotData = [],
      plotOptions = {
        animate: true,
        animateReplot: true,
        cursor: {
          showTooltip: false
        },
        series:[
          {
            pointLabels: {
              show: true
            },
            renderer: $.jqplot.BarRenderer,
            showHighlight: false,
            rendererOptions: {
              animation: {
                speed: 2500
              },
              barWidth: 15,
              barPadding: -15,
              barMargin: 0,
              highlightMouseOver: false
            },
            label: samStrs.labels.hits
          },
          {
            label: samStrs.labels.clicks,
            rendererOptions: {
              animation: {
                speed: 2000
              }
            }
          }
        ],
        axesDefaults: {
          pad: 0
        },
        axes: {
          xaxis: {
            tickInterval: 1,
            drawMajorGridlines: false,
            drawMinorGridlines: true,
            drawMajorTickMarks: false,
            rendererOptions: {
              tickInset: 1,
              minorTicks: 1
            },
            min: 1
          },
          yaxis: {
            rendererOptions: {
              forceTickAt0: true
            }
          }
        },
        highlighter: {
          show: true,
          showLabel: true,
          tooltipAxes: 'y',
          sizeAdjust: 7.5 ,
          tooltipLocation : 'ne',
          useAxesFormatters: false,
          tooltipFormatString: samStrs.labels.clicks + ': %d'
        },
        legend: {
          show: true,
          placement: 'ne'
        }
      };

    function buildLGrid(name, grid, vi, field, gc, url, ss) {
      var iVal = vi.val();
      grid.w2grid({
        name: name,
        show: {
          selectColumn: true,
          toolbar: true,
          footer: true
        },
        multiSearch: true,
        searches: ss,
        multiSelect: true,
        columns: gc,
        url: url,
        limit: 10000,
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
        },
        onLoad: function(event) {
          event.onComplete = function() {
            if(null != iVal && '' != iVal) {
              var arr = iVal.split(',');

              $.each(arr, function(i, val) {
                $.each(w2ui[name].records, function(index, value) {
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
        }
      });
    }

    function buildGrid(name, grid, vi, field, gc, gr) {
      //grid = $('#' + name);
      //vi = $('#' + vn);
      var iVal = vi.val();
      grid.w2grid({
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

    title.tooltip({
      track: true
    });
    $('#image_tools').tabs();

    media.init();

    stats = $.post(samStatsUrl, {
      action: 'load_item_stats',
      id: itemId,
      sm: sMonth
    }).done(function(data) {
      if(iAction != 'new') {
        $('#total_hits').text(data.total.hits);
        $('#total_clicks').text(data.total.clicks);
        plotData = [data.hits, data.clicks];
        plot = $.jqplot('graph', plotData, plotOptions);
      }
    });

    // Advertiser ComboGrid
    $('#adv_nick').combogrid({
      url: samAjaxUrl + '?action=load_combo_data',
      datatype: "json",
      munit: 'px',
      alternate: true,
      colModel: models.comboGrid,
      select: function(event, ui) {
        $('#adv_nick').val(ui.item.slug);
        $('#adv_name').val(ui.item.title);
        $('#adv_mail').val(ui.item.email);
        return false;
      }
    });

    $("#add-file-button").click(function () {
      var curFile = samStrs.url + $("select#files_list option:selected").val();
      $("#ad_img").val(curFile);
      return false;
    });

    buildGrid('ctt-grid', cttGrid, cttIn, 'slug', models.customTaxes, gData.cTax);
    buildGrid('x-ctt-grid', xcttGrid, xcttIn, 'slug', models.customTaxes, gData.cTax);
    buildGrid('cust-grid', custGrid, custIn, 'slug', models.customs, gData.customs);
    buildGrid('x-cust-grid', xcustGrid, xcustIn, 'slug', models.customs, gData.customs);

    var
      postAjax =
        samAjaxUrl +
        '?action=load_posts&cstr=' + samEditorOptions.data.custList +
          '&sp=' + sPost + '&spg=' + sPage + '&limit=10000';
    buildLGrid('posts-grid', postsGrid, postsIn, 'id', models.posts, postAjax, searches.posts);
    buildLGrid('x-posts-grid', xpostsGrid, xpostsIn, 'id', models.posts, postAjax, searches.posts);

    $('#tabs').tabs({
      activate: function( event, ui ) {
        var el = ui.newPanel[0].id;
        if(el == 'tabs-1') {
          if(w2ui['posts-grid']) postsGrid.w2render('posts-grid');
        }
        if(el == 'tabs-2') {
          if(rcctt.is(':visible') && w2ui['ctt-grid']) cttGrid.w2render('ctt-grid');
          if(rcxct.is(':visible') && w2ui['x-ctt-grid']) xcttGrid.w2render('x-ctt-grid');
          if(rcxid.is(':visible') && w2ui['x-posts-grid']) xpostsGrid.w2render('x-posts-grid');
          if(rcac.is(':visible') && w2ui['cats-grid']) catsGrid.w2render('cats-grid');
          if(rcxc.is(':visible') && w2ui['x-cats-grid']) xcatsGrid.w2render('x-cats-grid');
          if(rcau.is(':visible') && w2ui['auth-grid']) authGrid.w2render('auth-grid');
          if(rcxa.is(':visible') && w2ui['x-auth-grid']) xauthGrid.w2render('x-auth-grid');
          if(rcat.is(':visible') && w2ui['tags-grid']) tagsGrid.w2render('tags-grid');
          if(rcxt.is(':visible') && w2ui['x-tags-grid']) xtagsGrid.w2render('x-tags-grid');
          if(rccu.is(':visible') && w2ui['cust-grid']) custGrid.w2render('cust-grid');
          if(rcxu.is(':visible') && w2ui['xcust-grid']) xcustGrid.w2render('x-cust-grid');
        }
        if(el == 'tabs-3')
          if(xViewUsers.is(':visible') && w2ui['users-grid']) usersGrid.w2render('users-grid');
        if(el == 'tabs-5') {
          if(plot && iAction != 'new') {
            plot.destroy();
            plot = $.jqplot('graph', plotData, plotOptions);
          }
        }
      }
    });

    $(window).resize(function() {
      if(plot && iAction != 'new') {
        plot.destroy();
        plot = $.jqplot('graph', plotData, plotOptions);
      }
    });

    $('#code_mode_false').click(function () {
      rccmf.show('blind', {direction:'vertical'}, 500);
      rccmt.hide('blind', {direction:'vertical'}, 500);
    });

    $('#code_mode_true').click(function () {
      rccmf.hide('blind', {direction:'vertical'}, 500);
      rccmt.show('blind', {direction:'vertical'}, 500);
    });

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
              if(w2ui['posts-grid']) postsGrid.w2render('posts-grid');
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

    var authRequest = $.ajax({
      url:samAjaxUrl,
      data: {
        action: 'load_authors'
      },
      type: 'POST'
    }), authData;

    authRequest.done(function(data) {
      authData = data;
      buildGrid('auth-grid', authGrid, authIn, 'slug', models.authors, authData);
      buildGrid('x-auth-grid', xauthGrid, xauthIn, 'slug', models.authors, authData);
    });

    var usersRequest = $.ajax({
      url: samAjaxUrl,
      data: {
        action: 'load_users',
        subscriber: encodeURI(samStrs.subscriber),
        contributor: encodeURI(samStrs.contributor),
        author: encodeURI(samStrs.author),
        editor: encodeURI(samStrs.editor),
        admin: encodeURI(samStrs.admin),
        sadmin: encodeURI(samStrs.superAdmin)
      },
      type: 'POST'
    }), usersData;

    usersRequest.done(function(data) {
      usersData = data;
      buildGrid('users-grid', usersGrid, usersIn, 'slug', models.users, usersData);
    });

    var catsRequest = $.ajax({
      url: samAjaxUrl,
      data: {
        action: 'load_cats'
      },
      type: 'POST'
    }), catsData;

    catsRequest.done(function(data) {
      catsData = data;
      buildGrid('cats-grid', catsGrid, catsIn, 'slug', models.cats, catsData);
      buildGrid('x-cats-grid', xcatsGrid, xcatsIn, 'slug', models.cats, catsData);
    });

    var tagsRequest = $.ajax({
      url: samAjaxUrl,
      data: {
        action: 'load_tags'
      },
      type: 'POST'
    }), tagsData;

    tagsRequest.done(function(data) {
      tagsData = data;
      buildGrid('tags-grid', tagsGrid, tagsIn, 'slug', models.tags, tagsData);
      buildGrid('x-tags-grid', xtagsGrid, xtagsIn, 'slug', models.tags, tagsData);
    });

    xId.click(function () {
      if (xId.is(':checked')) {
        if(2 == $('input:radio[name=view_type]:checked').val()) {
          xId.attr('checked', false);
        }
        else
          rcxid.show('blind', {direction:'vertical'}, 500, function() {
            if(w2ui['x-posts-grid']) xpostsGrid.w2render('x-posts-grid');
          });
      }
      else rcxid.hide('blind', {direction:'vertical'}, 500);
    });

    $("input:radio[name=ad_users]").click(function() {
      var uval = $('input:radio[name=ad_users]:checked').val();
      if(uval == '0') {
        if(custUsers.is(':visible')) custUsers.hide('blind', {direction:'vertical'}, 500);
      }
      else {
        if(custUsers.is(':hidden'))
          custUsers.show('blind', {direction:'vertical'}, 500, function() {
            if(xViewUsers.is(':visible') && w2ui['users-grid']) usersGrid.w2render('users-grid');
          });
      }
    });

    adUsersReg.click(function() {
      if(adUsersReg.is(':checked'))
        xRegUsers.show('blind', {direction:'vertical'}, 500, function() {
          if(xViewUsers.is(':visible') && w2ui['users-grid']) usersGrid.w2render('users-grid');
        });
      else xRegUsers.hide('blind', {direction:'vertical'}, 500);
    });

    xAdUsers.click(function() {
      if(xAdUsers.is(':checked'))
        xViewUsers.show('blind', {direction:'vertical'}, 500, function() {
          if(w2ui['users-grid']) usersGrid.w2render('users-grid');
        });
      else xViewUsers.hide('blind', {direction:'vertical'}, 500);
    });

    $('#ad_swf').click(function() {
      if($('#ad_swf').is(':checked')) $('#swf-params').show('blind', {direction:'vertical'}, 500);
      else $('#swf-params').hide('blind', {direction:'vertical'}, 500);
    });

    if(adCats.is(':checked') && xCats.is(':checked')) {
      xCats.attr('checked', false);
      rcxc.hide('blind', {direction:'vertical'}, 500);
    }

    adCats.click(function () {
      if (adCats.is(':checked')) {
        rcac.show('blind', {direction:'vertical'}, 500, function() {
          if(w2ui['cats-grid']) catsGrid.w2render('cats-grid');
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
          if(w2ui['x-cats-grid']) xcatsGrid.w2render('x-cats-grid');
        });
        if(adCats.is(':checked')) {
          adCats.attr('checked', false);
          rcac.hide('blind', {direction:'vertical'}, 500);
          acw.hide('blind', {direction:'vertical'}, 500);
        }
      }
      else rcxc.hide('blind', {direction:'vertical'}, 500);
    });

    if(actt.is(':checked') && xacct.is(':checked')) {
      xacct.attr('checked', false);
      rcxct.hide('blind', {direction:'vertical'}, 500);
    }

    actt.click(function() {
      if(actt.is(':checked')) {
        rcctt.show('blind', {direction: 'vertical'}, 500, function() {
          if(w2ui['ctt-grid']) cttGrid.w2render('ctt-grid');
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
          if(w2ui['x-ctt-grid']) xcttGrid.w2render('x-ctt-grid');
        });
        if(actt.is(':checked')) {
          actt.attr('checked', false);
          rcctt.hide('blind', {direction:'vertical'}, 500);
          cttw.hide('blind', {direction:'vertical'}, 500);
        }
      }
      else rcxct.hide('blind', {direction: 'vertical'}, 500);
    });

    if(adAuth.is(':checked') && xAuth.is(':checked')) {
      xAuth.attr('checked', false);
      rcxa.hide('blind', {direction:'vertical'}, 500);
    }

    adAuth.click(function () {
      if (adAuth.is(':checked')) {
        rcau.show('blind', {direction:'vertical'}, 500, function() {
          if(w2ui['auth-grid']) authGrid.w2render('auth-grid');
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
          if(w2ui['x-auth-grid']) xauthGrid.w2render('x-auth-grid');
        });
        if(adAuth.is(':checked')) {
          adAuth.attr('checked', false);
          rcau.hide('blind', {direction:'vertical'}, 500);
          aaw.hide('blind', {direction:'vertical'}, 500);
        }
      }
      else rcxa.hide('blind', {direction:'vertical'}, 500);
    });

    if(adTags.is(':checked') && xTags.is(':checked')) {
      xTags.attr('checked', false);
      rcxt.hide('blind', {direction:'vertical'}, 500);
    }

    adTags.click(function () {
      if (adTags.is(':checked')) {
        rcat.show('blind', {direction:'vertical'}, 500, function() {
          if(w2ui['tags-grid']) tagsGrid.w2render('tags-grid');
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
          if(w2ui['x-tags-grid']) xtagsGrid.w2render('x-tags-grid');
        });
        if(adTags.is(':checked')) {
          adTags.attr('checked', false);
          rcat.hide('blind', {direction:'vertical'}, 500);
          atw.hide('blind', {direction:'vertical'}, 500);
        }
      }
      else rcxt.hide('blind', {direction:'vertical'}, 500);
    });

    if(adCust.is(':checked') && xCust.is(':checked')) {
      xCust.attr('checked', false);
      rcxu.hide('blind', {direction:'vertical'}, 500);
    }

    adCust.click(function () {
      if (adCust.is(':checked')) {
        rccu.show('blind', {direction:'vertical'}, 500, function() {
          if(w2ui['cust-grid']) custGrid.w2render('cust-grid');
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
          if(w2ui['x-cust-grid']) xcustGrid.w2render('x-cust-grid');
        });
        if(adCust.is(':checked')) {
          adCust.attr('checked', false);
          rccu.hide('blind', {direction:'vertical'}, 500);
          cuw.hide('blind', {direction:'vertical'}, 500);
        }
      }
      else rcxu.hide('blind', {direction:'vertical'}, 500);
    });

    $("#ad_start_date, #ad_end_date").datepicker({
      dateFormat:'yy-mm-dd',
      showButtonPanel:true
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

    sPointer = samEditorOptions.ads;
    sPointer.pointer = 'ads';

    if(sPointer.enabled || '' == title.val()) {
      title.pointer({
        content: '<h3>' + sPointer.title + '</h3><p>' + sPointer.content + '</p>',
        position: 'top',
        close: function() {
          $.ajax({
            url: ajaxurl,
            data: {
              action: 'close_sam_pointer',
              pointer: sPointer.pointer
            },
            async: true
          });
        }
      }).pointer('open');
    }

    var
      isSing = $('#is_singular'), isSingle = $('#is_single'), isPage = $('#is_page'), isAttach = $('#is_attachment'), isPostType = $('#is_posttype');

    isSing.click(function () {
      if (isSing.is(':checked'))
        $('#is_single, #is_page, #is_attachment, #is_posttype').attr('checked', true);
    });

    $('#is_single, #is_page, #is_attachment, #is_posttype').click(function () {
      if (isSing.is(':checked') &&
        (!isSingle.is(':checked') ||
          !isPage.is(':checked') ||
          !isAttach.is(':checked') ||
          !isPostType.is(':checked') )) {
        isSing.attr('checked', false);
      }
      else {
        if (!isSing.is(':checked') &&
          isSingle.is(':checked') &&
          isPostType.is(':checked') &&
          isPage.is(':checked') &&
          isAttach.is(':checked'))
          isSing.attr('checked', true);
      }
    });

    var
      isArc = $('#is_archive'), isTax = $('#is_tax'), isCat = $('#is_category'), isTag = $('#is_tag'),
      isAuthor = $('#is_author'), isDate = $('#is_date'), isPostTypeArc = $('#is_posttype_archive'),
      archives = $('#is_tax, #is_category, #is_tag, #is_author, #is_date, #is_posttype_archive');

    isArc.click(function () {
      if (isArc.is(':checked')) archives.attr('checked', true);
    });

    archives.click(function () {
      if (isArc.is(':checked') &&
        (!isTax.is(':checked') ||
          !isCat.is(':checked') ||
          !isPostTypeArc.is(':checked') ||
          !isTag.is(':checked') ||
          !isAuthor.is(':checked') ||
          !isDate.is(':checked'))) {
        isArc.attr('checked', false);
      }
      else {
        if (!isArc.is(':checked') &&
          isTax.is(':checked') &&
          isCat.is(':checked') &&
          isPostTypeArc.is(':checked') &&
          isTag.is(':checked') &&
          isAuthor.is(':checked') &&
          isDate.is(':checked')) {
          isArc.attr('checked', true);
        }
      }
    });

    $('#stats_month').change(function() {
      sMonth = $(this).val();
      $.post(samStatsUrl, {
        action: 'load_item_stats',
        id: itemId,
        sm: sMonth
      }).done(function(data) {
          $('#total_hits').text(data.total.hits);
          $('#total_clicks').text(data.total.clicks);
          plotData = [data.hits, data.clicks];
          if(plot && iAction != 'new') {
            plot.destroy();
            plot = $.jqplot('graph', plotData, plotOptions);
          }
        });
    });

    return false;
  });
})(jQuery);