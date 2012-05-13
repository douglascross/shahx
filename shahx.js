"use strict";

var shh = new (function () {
    var time = function () {
            return (new Date).getTime();
        },
        start = time(),
        requireTooLongTime = 2000,
        requiresCount = 0,
        requiresToDo = 0,
        listeners = {},
        requested = {},
        loaded = {},
        loadingCallback,
        onRequire = function (file) {
            loaded[file] = true;
            requiresToDo -= 1;
            listeners[file].forEach(function (listener) {
                listener();
            });
        };
        
    this.time = time;
    
    this.root = '';

    this.require = function (file, callback) {
        var script, newjs;

        if (!loaded[file]) {
            if (!listeners[file]) {
                listeners[file] = [];
            }

            if (callback) {
                listeners[file].push(callback);
            }

            if (!requested[file]) {          
                script = document.getElementsByTagName('script')[0];
                newjs = document.createElement('script');
            
                requested[file] = true;
                    
                requiresCount += 1;
                requiresToDo += 1;
                    
                // IE 
                newjs.onreadystatechange = function () {
                    if (newjs.readyState === 'loaded' || newjs.readyState === 'complete') {
                        newjs.onreadystatechange = null;
                        onRequire(file);
                    }
                };
                
                // others
                newjs.onload = function () {
                    onRequire(file);
                };
                
                newjs.src = (DH.root ? DH.root : '') + file;
                script.parentNode.insertBefore(newjs, script);
            }
        } else if (callback) {
            callback();
        }
    };
    
    this.requireReady = function (callback) {
        var lastChange = start,
            lastLoaded = 0,
            requiresLoaded = 0,
            timer = setInterval(function () {
                var now = time(),
                    cfg,
                    tooLong = false;
                requiresLoaded = requiresCount - requiresToDo;
                if (requiresLoaded !== lastLoaded) {
                    lastChange = now;
                }
                if (now - lastChange > requireTooLongTime) {
                    tooLong = true;
                }
                if (loadingCallback) {
                    cfg = {
                        total: requiresCount,
                        remainder: requiresToDo
                    };
                    if (tooLong) {
                        cfg.error = true;
                        cfg.tooLong = true;
                    }
                    loadingCallback(cfg);
                } 
                if (requiresToDo === 0 || tooLong) {
                    clearInterval(timer);
                    callback();
                }
                lastLoaded = requiresLoaded;
            }, 15);
    };
    
    this.loading = function (callback) {
        loadingCallback = callback;
    };
    
    this.ready = function (callback) {
        this.requireReady(callback);
    };
});
