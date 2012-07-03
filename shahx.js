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
    
    this.src = {shh: 'shahx/'};
    
    this.markLoaded = function (files) {
    	files.forEach(function (file) {
    		loaded[file] = true;
    		if (requested[file]) {
	            requiresToDo -= 1;
	            listeners[file].forEach(function (listener) {
	                listener();
	            });
    		}
    	});
    };

    this.fileRequire = function (file, callback) {
        var script, fileref, type, isCss,
        	arr = file.split(':'),
        	scheme = arr[1] ? arr[0] : '',
        	pfx = shh.src[scheme];
        pfx = pfx ? pfx : scheme + '/';
        file = (pfx ? pfx : '') + arr[scheme ? 1 : 0];

        if (!loaded[file]) {
            if (!listeners[file]) {
                listeners[file] = [];
            }

            if (callback) {
                listeners[file].push(callback);
            }

            if (!requested[file]) {
            	type = file.split('.').reverse()[0];
            	isCss = type === 'css';
            	          
                script = document.getElementsByTagName('script')[0];
                fileref = document.createElement(isCss ? 'link' : 'script');
            
                requested[file] = true;
                    
                requiresCount += 1;
                requiresToDo += 1;
                    
                // IE 
                fileref.onreadystatechange = function () {
                    if (newjs.readyState === 'loaded' || newjs.readyState === 'complete') {
                        newjs.onreadystatechange = null;
                        onRequire(file);
                    }
                };
                
                // others
                fileref.onload = function () {
                    onRequire(file);
                };
                
                if (isCss) {
                	fileref.setAttribute("rel", "stylesheet");
                	fileref.setAttribute("type", "text/css");
                }
                
                fileref[isCss ? 'href' : 'src'] = file + '?' + (new Date).getTime();
                script.parentNode.insertBefore(fileref, script);
            }
        } else if (callback) {
            callback();
        }
    };
    
    this.require = function (name, callback) {
    	this.fileRequire(name, callback);
    }
    
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
