
shh.class('shh.lib.View', {

    extends: 'shh.lib.Data',
    
    construct: function (cfg) {
        shh.lib.View.superclass.construct.call(this, cfg);
        if (cfg && cfg.node) {
            this.draw();
        }
    },
    
    draw: function () {
    },
    
    refresh: function () {
    }
});
