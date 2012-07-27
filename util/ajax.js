shh.require("shh:util/ecma.js");
shh.require("shh:util/type.js");

shh.request = function (cfg) {
	var ajax = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP"),
		fn = cfg.fn || function () {},
		scope = cfg.scope || this,
		time = (new Date).getTime(),
		data = cfg.data,
		url = this.formatFile(cfg.url || document.href),
		uri;
	if (!ajax) {
		fn.call(scope);
		return false;
	}
	ajax.onreadystatechange = function () {
        if (ajax.readyState==4) {             
			fn.call(scope, ajax.responseText);        
        }                                                    
	}
	if (cfg.method === 'POST') {
		uri = url + '?' + time;
        ajax.open("POST", uri, true);
        ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajax.setRequestHeader("Content-Length", data.length);
        ajax.send(data);
	} else {
		uri = url + '?' + data + (data ? '&' : '') + time;
        ajax.open("GET", uri, true);                             
        ajax.send(null);                                         
	}
};
