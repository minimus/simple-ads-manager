(function() {
  tinymce.PluginManager.requireLangPack('samb');

  tinymce.create('tinymce.plugins.samb', {

    init : function(ed, url) {
      this.editor = ed;

      ed.addCommand('samAd', function() {
        ed.windowManager.open({
          file: url + '/dialog-ad.php',
          width: 450 + parseInt(ed.getLang('samb.delta_width', 0)),
          height: 280 + parseInt(ed.getLang('samb.delta_height', 0)),
          inline : 1
        }, {
          plugin_url: url
        });
      });

      ed.addCommand('samPlace', function() {
        ed.windowManager.open({
          file: url + '/dialog.php',
          width: 450 + parseInt(ed.getLang('samb.delta_width', 0)),
          height: 280 + parseInt(ed.getLang('samb.delta_height', 0)),
          inline: 1
        }, {
          plugin_url: url
        });
      });

      ed.addCommand('samZone', function() {
        ed.windowManager.open({
          file: url + '/dialog-zone.php',
          width: 450 + parseInt(ed.getLang('samb.delta_width', 0)),
          height: 280 + parseInt(ed.getLang('samb.delta_height', 0)),
          inline: 1
        }, {
          plugin_url: url
        });
      });

      ed.addCommand('samBlock', function() {
        ed.windowManager.open({
          file: url + '/dialog-block.php',
          width: 450 + parseInt(ed.getLang('samb.delta_width', 0)),
          height: 280 + parseInt(ed.getLang('samb.delta_height', 0)),
          inline: 1
        }, {
          plugin_url: url
        });
      });

      ed.addButton('sama', {title: 'samb.ad', cmd: 'samAd', image: url + '/img/sam.png'});
      ed.addButton('samp', {title: 'samb.place', cmd: 'samPlace', image: url + '/img/sam-place.png'});
      ed.addButton('samz', {title: 'samb.zone', cmd: 'samZone', image: url + '/img/sam-zone.png'});
      ed.addButton('samb', {title: 'samb.block', cmd: 'samBlock', image: url + '/img/sam-block.png'});
    },

    getInfo : function() {
      return {
        longname  : 'Simple Ads Manager',
        author     : 'minimus',
        authorurl : 'http://blogcoding.ru/',
        infourl   : 'http://www.simplelib.com/',
        version   : "1.1.38"
      };
    }
  });

  tinymce.PluginManager.add('samb', tinymce.plugins.samb);
})();