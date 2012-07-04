(function() {
  var samUrl = '';
  
  tinymce.PluginManager.requireLangPack('samb');
   
  tinymce.create('tinymce.plugins.samb', {
    
    init : function(ed, url) {
      samUrl = url;
    },
    
    createControl : function(n, cm) {
      switch (n) {
        case 'samb':
          var c = cm.createSplitButton('samb', {
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