var CONF = require('../conf/conf.js');
var API = require('../conf/api.js');
var Http = require('../util/http.js');
var Auth = require('../util/auth.js');

var module_common = function(router){

  // 获取分类
  router.get('/attr', function(req, res, next){
       var params = req.query||{};
       Http({url: API.attr.replace('{attr_group_id}',params.attr_group_id), method: 'GET'},params,//前端数据
        function (content){
            res.send(content);
        }, req);
  });
   

};

module.exports = module_common;
