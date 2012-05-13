shh.global = (function () {
    return this;
}());

shh.namespace = function (namespace) {
    var names = namespace.split('.'),
        build = this.global;
    
    names.forEach(function (name) {
        if (!build[name]) {
            build[name] = {};
        }
        build = build[name];
    }, this);
    
    return build;
}
