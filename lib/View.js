
shh.create('shh.lib.View', {

    extend: 'shh.lib.Data',
    
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
