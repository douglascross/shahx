if (!Array.prototype.forEach) {
    Array.prototype.forEach = function (fn) {
        var len = this.length, i = 0,
            scope = arguments[1];

        for (; i < len; i += 1) {
            fn.call(scope, this[i], i, this);
        }
    };
}

if (!String.prototype.trim) {
	String.prototype.trim = function () {
		return this.replace(/^\s+|\s+$/g, '');
	};
}
