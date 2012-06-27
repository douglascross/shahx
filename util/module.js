shh.require('shh:util/class.js');

// Create this function from a function in an object instance created on-the-fly. The reason for this is to utilize 
// some scoped variables as static.
if (!shh.module) {
    shh.apply(shh, new function () {
        
        this.module = function (name, config) {
        	var requireArray;
        	if (config) {
        		requireArray = shh.toArray(config.require);
	            requireArray.forEach(function (require) {
	            	shh.require(require);
	            });
        		if (config.ready) {
        			shh.ready(function () {
        				config.ready.call();
        			});
        		}
			}
        };

    });
}
