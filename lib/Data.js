shh.require('shh:util/uuid.js');

shh.create('shh.lib.Data', {

    id: null,
    parent: null,
    
    construct: function (cfg) {
        this.init();
        this.set(cfg);
    },
    
    init: function () {
    },
    
    set: function (cfg) {
        if (cfg) {
            shh.apply(this, cfg);
            if (!this.id) {
                this.id = shh.uuid();
            }
        }
    },
    
    toJSON: function () {
        var json = {},
            n;
        for (n in this) {
            if (this.hasOwnProperty(n) && typeof n !== 'function') {
                if (this[n] instanceof shh.lib.Data) {
                    json[n] = this[n].toJSON();
                } else {
                    json[n] = this[n];
                }
            }
        }
        return json;
    }
});
