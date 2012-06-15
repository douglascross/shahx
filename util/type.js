shh.isArray = function (o) {
	return o && !!o.length;
};
shh.toArray = function (o) {
	return shh.isArray(o) ? o : [o];
};
