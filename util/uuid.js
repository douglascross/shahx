shh.uuid = (function () {
    var math = Math,
        floor = math.floor,
        random = math.random,
        x = function (amount) { 
            var i, result = '';
            for (i = 0; i < amount; i += 1) { 
                result += floor(random() * 16).toString(16); 
            }
            return result;
        },
        y = function () { 
            return floor(random() * 4 + 8).toString(16); 
        };
    return function () {
        return [x(8), '-', x(4), '-', 4, x(3), '-', y(), x(3), '-', x(12)].join('');
    };
}());
