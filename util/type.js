shh.isArray = function (o) {
	return o && (o.length !== undefined) && typeof(o) !== 'string';
};
shh.toArray = function (o) {
	return shh.isArray(o) ? o : o === undefined ? [] : [o];
};
shh.isString = function (o) {
	return typeof(o) === 'string';
};
shh.isEl = function (o) {
	return typeof(o) === 'object' && o !== undefined && o.tagName;
}
