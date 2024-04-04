
var jwtDecode = function(jwt) {
    var tokens = jwt.split('.');
    return JSON.parse(atob(tokens[1]));
};


