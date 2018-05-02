(function() {
    tinymce.create('tinymce.plugins.wpcache', {
        init : function(ed, url) {
            url = url.replace("../js","../images");
            ed.addButton('wpcache', {
                title : 'Block caching for this page',
                cmd : 'wpcache',
                image : url + "/icon.png"
            });

            ed.addCommand('wpcache', function() {
                ed.execCommand('mceInsertContent', 0, "[NoCache]");
            });
        }
    });
    tinymce.PluginManager.add( 'wpcache', tinymce.plugins.wpcache );
})();