shh.require('shh:util/class.js');

// Create this function from a function in an object instance created on-the-fly. The reason for this is to utilize 
// some scoped variables as static.
if (!shh.module) {
    shh.apply(shh, new function () {
        
        this.module = function (name, config) {
			config.ready.call();
        };

    });
}
