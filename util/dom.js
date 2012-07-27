shh.require("shh:util/ecma.js");
shh.require("shh:util/type.js");

shh.el = function (cfg) {
	var el = document.createElement(cfg.tag || "div"),
		items = cfg.items || [],
		attr,
		ex = {tag: 1, html: 1, text: 1, items: 1},
		rn = {cls: "class"};
	for (name in cfg) {
		if (cfg.hasOwnProperty(name) && !ex[name]) {
			attr = document.createAttribute(rn[name] || name);
			attr.nodeValue = cfg[name];
			el.setAttributeNode(attr);
		}
	}
	if (cfg.html) {
		el.innerHTML = cfg.html;
	}
	if (cfg.text) {
		el.appendChild(document.createTextNode(cfg.text));
	}
	items.forEach(function(item) {
		el.appendChild(shh.el(item));
	});
	return el;
};

shh.append = function (src, dst) {
    src = shh.get(src);
	(shh.get(dst) || document.getElementsByTagName("body")[0]).appendChild(src);
	return src;
};

shh.get = function (qry) {
	 return qry ? shh.isString(qry) ? document.getElementById(qry) : shh.isEl(qry) ? qry : shh.el(qry) : null;
};
