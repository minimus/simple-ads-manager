/**
 * Created by minimus on 17.11.13.
 */
var sam = sam || {};
(function ($) {
  var media, mediaTexts = samEditorOptions.media;

  sam.media = media = {
    buttonId: '#banner-media',
    adUrl: '#patch_img',
    adImgId: '#patch_img_id',
    adName: '#title',
    adDesc: '#description',
    //adAlt: '#ad_alt',

    init: function() {
      $(this.buttonId).on( 'click', this.openMediaDialog );
    },

    openMediaDialog: function( e ) {
      e.preventDefault();

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
        media.handleMediaAttachment( attachment );
      });

      this._frame.open();
    },

    handleMediaAttachment: function(a) {
      var attechment = a.toJSON();
      $(this.adUrl).val(attechment.url);
      $(this.adImgId).val(attechment.id);
      if('' == $(this.adName).val() && '' != attechment.title) $(this.adName).val(attechment.title);
      if('' == $(this.adDesc).val() && '' != attechment.caption) $(this.adDesc).val(attechment.caption);
      if('' == $(this.adAlt).val() && '' != attechment.alt) $(this.adAlt).val(attechment.alt);
    }
  };

  $(document).ready(function () {
    var em = $('#editor_mode').val(), fu;

    var
      rcpsi = $('#rc-psi'), rcpsc = $('#rc-psc'), rcpsd = $('#rc-psd'), title = $("#title");

    var
      itemId = $('#place_id').val(),
      btnUpload = $("#upload-file-button"),
      status = $("#uploading"),
      srcHelp = $("#uploading-help"),
      loadImg = $('#load_img'),
      sPointer, sMonth = 0,
      grid = $('#ads-grid'),
      samStatsUrl = samEditorOptions.samStatsUrl,
      labels = samEditorOptions.labels;

    sPointer = samEditorOptions.places;
    sPointer.pointer = 'places';

    /*var samUploader, mediaTexts = samEditorOptions.media;*/

    media.init();

    title.tooltip({
      track: true
    });

    var options = samEditorOptions.options, plot, plotData = [],
      plotOptions = {
        animate: true,
        // Will animate plot on calls to plot1.replot({resetAxes:true})
        animateReplot: true,
        cursor: {
          //show: true,
          //zoom: true,
          //looseZoom: true,
          showTooltip: false
        },
        series:[
          {
            pointLabels: {
              show: true
            },
            renderer: $.jqplot.BarRenderer,
            showHighlight: false,
            //yaxis: 'y2axis',
            rendererOptions: {
              // Speed up the animation a little bit.
              // This is a number of milliseconds.
              // Default for bar series is 3000.
              animation: {
                speed: 2500
              },
              barWidth: 15,
              barPadding: -15,
              barMargin: 0,
              highlightMouseOver: false
            },
            label: labels.hits
          },
          {
            label: labels.clicks,
            rendererOptions: {
              // speed up the animation a little bit.
              // This is a number of milliseconds.
              // Default for a line series is 2500.
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
          // These options will set up the x axis like a category axis.
          xaxis: {
            tickInterval: 1,
            drawMajorGridlines: false,
            drawMinorGridlines: true,
            drawMajorTickMarks: false,
            rendererOptions: {
              tickInset: 1, //0.5,
              minorTicks: 1
            },
            //padMin: 0,
            min: 1
          },
          yaxis: {
            /*tickOptions: {
             formatString: "%'d"
             },*/
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
          tooltipFormatString: labels.clicks + ': %d'
        },
        legend: {
          show: true,
          placement: 'ne'
        }
      };

    /*var
      uConsole = $('#upload-console'),
      progress = $('#upload-progress'),
      uploadOptions = samEditorOptions.uploader;

    var uploader = new plupload.Uploader({
      browse_button: 'upload-file-button',
      url: uploadOptions.url + '?path=' + uploadOptions.path,
      multi_selection: false,
      filters: {
        max_file_size : '500kb',
        mime_types: [
          { title: "Image file", extensions: "jpg,jpeg,gif,png" },
          { title: "Flash file", extensions: "swf" }
        ]
      },
      init: {
        PostInit: function() {
          uConsole.text('');
          progress.text('');
        },
        FilesAdded: function(up, files) {
          plupload.each(files, function(file) {
            uConsole.text(file.name);
          });
          this.start();
        },
        UploadProgress: function(up, file) {
          progress.text(file.percent + '%');
        },
        UploadComplete: function(up, files) {
          uConsole.text('');
          progress.text('');
          $('<div id="files"></div>').appendTo(srcHelp);
          $("#patch_img").val(uploadOptions.adUrl + files[0].name);
          $("#files").html('<p>' + options.file + ' ' + files[0].name + ' ' + options.uploaded + '</p>')
            .addClass('updated')
            .delay(3000)
            .fadeOut(1000, function () {
              $(this).remove();
            });
        },
        Error: function(up, err) {
          $('<div id="files"></div>').appendTo(srcHelp);
          $('#files').html( '<p>Error(' +err.code + "): " + err.message + '</p>')
            .addClass('error')
            .delay(3000)
            .fadeOut(1000, function () {
              $(this).remove();
            });
        }
      }
    });

    uploader.init();*/

    $.post(samStatsUrl, {
      action: 'load_stats',
      id: itemId,
      sm: sMonth
    }).done(function(data) {
        plotData = [data.hits, data.clicks];
        $('#total_hits').text(data.total.hits);
        $('#total_clicks').text(data.total.clicks);
        plot = $.jqplot('graph', plotData, plotOptions);
      });

    $('#tabs').tabs({
      activate: function(ev, ui) {
        var el = ui.newPanel[0].id;
        if(el == 'tabs-3') {
          if(plot) {
            plot.destroy();
            plot = $.jqplot('graph', plotData, plotOptions);
          }
        }
      }
    });

    $(window).resize(function() {
      if(plot) {
        plot.destroy();
        plot = $.jqplot('graph', plotData, plotOptions);
      }
    });

    $('#image_tools').tabs();

    $.post(samStatsUrl, {
      action: 'load_ads',
      id: itemId,
      sm: 0
    }).done(function(data) {
        var records = data.records;
        grid.w2grid({
          name: 'ads',
          show: {selectColumn: false},
          multiSelect: false,
          columns: samEditorOptions.columns,
          records: records
        });
      });
    /*var url = samStatsUrl + '?action=load_ads&id=' + itemId + '&sm=0';
    grid.w2render('ads-grid');*/

    $("#add-file-button").click(function () {
      var curFile = options.url + $("select#files_list option:selected").val();
      $("#patch_img").val(curFile);
      return false;
    });

    $('#patch_source_image').click(function () {
      if (rcpsi.is(':hidden')) rcpsi.show('blind', {direction:'vertical'}, 500);
      if (rcpsc.is(':visible')) rcpsc.hide('blind', {direction:'vertical'}, 500);
      if (rcpsd.is(':visible')) rcpsd.hide('blind', {direction:'vertical'}, 500);
    });

    $('#patch_source_code').click(function () {
      if (rcpsi.is(':visible')) rcpsi.hide('blind', {direction:'vertical'}, 500);
      if (rcpsc.is(':hidden')) rcpsc.show('blind', {direction:'vertical'}, 500);
      if (rcpsd.is(':visible')) rcpsd.hide('blind', {direction:'vertical'}, 500);
    });

    $('#patch_source_dfp').click(function () {
      if (rcpsi.is(':visible')) rcpsi.hide('blind', {direction:'vertical'}, 500);
      if (rcpsc.is(':visible')) rcpsc.hide('blind', {direction:'vertical'}, 500);
      if (rcpsd.is(':hidden')) rcpsd.show('blind', {direction:'vertical'}, 500);
    });

    if(sPointer.enabled || '' == title.val()) {
      $('#title').pointer({
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

    $('#stats_month').change(function() {
      sMonth = $(this).val();
      $.post(samStatsUrl, {
        action: 'load_stats',
        id: itemId,
        sm: sMonth
      }).done(function(data) {
          plotData = [data.hits, data.clicks];
          $('#total_hits').text(data.total.hits);
          $('#total_clicks').text(data.total.clicks);
          if(plot) {
            plot.destroy();
            plot = $.jqplot('graph', plotData, plotOptions);
          }
        });
    });

    return false;
  });
})(jQuery);