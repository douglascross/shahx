shh.require('shh/util/namespace.js');

shh.find = function (name) {
    var names = name.split('.'),
        build = this.global;
        
    names.forEach(function (name) {
        if (!build) {
            return;
        }
        build = build[name];
    }, this);

    return build;
};

shh.apply = function (target, config) {
    var n;
    for (n in config) {
        if(config.hasOwnProperty(n)) {
            target[n] = config[n];
        }
    }
    return target;
};

// Create this function from a function in an object instance created on-the-fly. The reason for this is to utilize 
// some scoped variables as static.
if (!shh.class) {
    shh.apply(shh, new function () {
        var listeners = {},
            created = {},
            buildsArray = [],
            buildsCount = 0,
            buildsToDo = 0,
            buildingCallback,
            // Triggered by a final build, it informs registered listeners.
            onBuild = function (name) {
                if (listeners[name]) {
                    listeners[name].forEach(function (listener) {
                        listener();
                    });
                }
            },
            // Register listeners for those who want to wait for the creation of the object [name].
            require = function (name, callback) {
                if (!created[name]) {
                    if (!listeners[name]) {
                        listeners[name] = [];
                    }

                    listeners[name].push(callback);

                    shh.require(name.split('.').join('/') + '.js');
                } else {
                    callback();
                }
            };
        
        this.class = function (name, config) {
            var extendsName,
                // Do the actual build.
                build = function () {
                    buildsToDo -= 1;
                    created[name] = true;
                    var names = name.split('.'),
                        lastName = names.pop(),
                        namespace = shh.namespace(names.join('.')),
                        Parent = extendsName ? shh.find(extendsName) : function () {},
                        Class, Bridge, noRun;
                        
                    Class = function () {
                        if (Class.construct) {
                            Class.construct.apply(this, arguments);
                        }
                    };
                        
                    Bridge = function () {};
                    Bridge.prototype = Parent.prototype;
                    Class.prototype = new Bridge();
                    Class.prototype.constructor = Class;
                    Class.superclass = Parent.prototype;
                    
                    // add config to prototype
                    shh.apply(Class.prototype, config);
                    
                    Class.construct = Class.prototype.construct;
                    
                    namespace[lastName] = Class;
                    
                    onBuild(name);
                };
            buildsCount += 1;
            buildsToDo += 1;
            buildsArray.push(name);
            if (config) {
                extendsName = config.extends;    
            }
            if (extendsName) {
                // A build with dependancy will have to be sure that what it is extending is built first.
                require(extendsName, build);
            } else {
                build();
            }
        };
        
        this.classReady = function (callback) {
            var timer = setInterval(function () {
                    if (buildingCallback) {
                        buildingCallback({
                            total: buildsCount,
                            remainder: buildsToDo
                        });
                    } 
                    if (buildsToDo <= 0) {
                        clearInterval(timer);
                        callback();
                    }
                }, 15);
        };
        
        this.building = function (callback) {
            buildingCallback = callback;
        };
        
        this.ready = function (callback) {
            this.requireReady((function (scope) {
                return function () {
                    scope.classReady(callback);
                };
            }(this)));
        };
    });
}
