(function() {
  var samUrl = '';
  
  tinymce.PluginManager.requireLangPack('samb');
   
  tinymce.create('tinymce.plugins.samb', {
    
    init : function(ed, url) {
      samUrl = url;
      this.editor = ed;

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

      ed.addButton('samb', {
        title: 'Insert Advertisement',
        type: 'splitbutton',
        image: url + '/img/sam.png',
        //icon: true,
        cmd: 'samPlace',
        menu: [
          {
            text: 'Insert Single Ad',
            image: url + '/img/sam.png',
            onClick: function() {
              ed.windowManager.open({
                file: url + '/dialog-ad.php',
                width: 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {plugin_url: url});
            }
          },
          {
            text: 'Insert Ads Place',
            onClick: function() {
              ed.windowManager.open({
                file : samUrl + '/dialog.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : url
              });
            }
          },
          {
            text: 'Insert Ads Zone',
            onClick: function() {
              ed.windowManager.open({
                file : samUrl + '/dialog-zone.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : url
              });
            }
          },
          {
            text: 'Insert Ads Block',
            onClick: function() {
              ed.windowManager.open({
                file : samUrl + '/dialog-block.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : url
              });
            }
          }
        ]
      });
    },
    
    /*createControl : function(n, cm) {
      switch (n) {
        case 'samb':
          var c = cm.ui.createSplitButton('samb', {
            title : 'samb.title',
            image : samUrl + '/img/sam.png',
            onclick : function() {
              var ed = tinyMCE.activeEditor;
              var se = ed.selection;
          
              ed.windowManager.open({
                file : samUrl + '/dialog.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : samUrl 
              });
            }
          });

          c.onRenderMenu.add(function(c, m) {
            m.add({title : 'samb.description', 'class' : 'mceMenuItemTitle'}).setDisabled(1);

            m.add({title : 'samb.ad', onclick : function() {
              var ed = tinyMCE.activeEditor;
              var se = ed.selection;
          
              ed.windowManager.open({
                file : samUrl + '/dialog-ad.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : samUrl 
              });
            }});

            m.add({title : 'samb.place', onclick : function() {
              var ed = tinyMCE.activeEditor;
              var se = ed.selection;
          
              ed.windowManager.open({
                file : samUrl + '/dialog.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : samUrl 
              });
            }});
            
            m.add({title : 'samb.zone', onclick : function() {
              var ed = tinyMCE.activeEditor;
              var se = ed.selection;
          
              ed.windowManager.open({
                file : samUrl + '/dialog-zone.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : samUrl 
              });
            }});
            
            m.add({title : 'samb.block', onclick : function() {
              var ed = tinyMCE.activeEditor;
              var se = ed.selection;
          
              ed.windowManager.open({
                file : samUrl + '/dialog-block.php',
                width : 450 + parseInt(ed.getLang('samb.delta_width', 0)),
                height : 280 + parseInt(ed.getLang('samb.delta_height', 0)),
                inline : 1
              }, {
                plugin_url : samUrl 
              });
            }});
          });

          // Return the new splitbutton instance
          return c;
        }

        return null;      
    },*/
    
    
    getInfo : function() {
      return {
          longname  : 'Simple Ads Manager',
          author     : 'minimus',
          authorurl : 'http://blogcoding.ru/',
          infourl   : 'http://www.simplelib.com/',
          version   : "2.3.85"
      };
    }
  });

  tinymce.PluginManager.add('samb', tinymce.plugins.samb);
})();