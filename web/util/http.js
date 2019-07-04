var request = require('request');
var JSONbig = require('json-bigint');
var _ = require('underscore');
var http = function (_opt, _body, callback, req, res) {

    // 每次请求都自动带上token信息
    if (req.session.user) {
        _body = _body || {};
        _body.token = req.session.user.token;
    }
    _body = JSON.stringify(_body);

    var opt = {
        url: _opt.url,
        method: _opt.method,
        headers: {}
    };

    if (_.isString(_body)) {
        opt.headers = {'Content-Type': 'application/json'};
        opt.body = _body;
    }

    request(opt, function (error, response, body) {
        if ( !error ) {
            var json;
            try {
                json = JSON.parse(body);
                if ( parseInt(json.code) === 4010 ){ // token失效自动检测
                    res.redirect('/login'); 
                } else {
                   typeof(callback)=='function'&& callback(json);
                }
            } catch(e) {
               // res.render('500');
               console.log(e);
               return;
            }
        }
        return true;
    })
};

module.exports = http;
