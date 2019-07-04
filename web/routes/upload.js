var CONF = require('../conf/conf.js');  // 配置文件
var API = require('../conf/api.js');    // 接口文档
var Http = require('../util/http.js');  // HTTP
var Auth = require('../util/auth.js');  // 网关
var request = require('request');       // 请求
var fs = require('fs');                 // 文件系统
var path = require('path');             // 文件路由
// 上传网关
var multipart = require('connect-multiparty');
var multipartMiddleware = multipart();

var module_upload= function (router) {

    //ueditor config 配置
    router.get('/lib/ueditor/1.4.3/php', multipartMiddleware, function (req, res, next) {
        var action = req.query.action;
        if (action == 'config') {
            var configFile = '/lib/ueditor/1.4.3/php/config.json';
            res.setHeader('Content-Type', 'application/json');
            res.redirect(configFile);
        }
    });

    //ueditor 上传
    router.post('/lib/ueditor/1.4.3/php', multipartMiddleware, function (req, res, next) {
        var action = req.query.action; 
        //上传视频
        if (action == 'uploadvideo') {
            //初始化
            var accessKey = 'J3VKqMiurI6ogDikJbOOBYuMzaln-K54gBZtCOUa';
            var secretKey = '4Qv-T5UwGflCyOmW0nnAW12FCb5HlgvACCvqT9ic';
            var mac = new qiniu.auth.digest.Mac(accessKey, secretKey);
            var bucket = 'rmrbpublic';
            //获取上传凭证
            var options = {
                scope: bucket,
                //将多个数据处理指令拼接起来
                "persistentOps": "avthumb/mp4/s/640x360/vb/1.25m",
                //数据处理队列名称，必填
                "persistentPipeline": "rmrbmedia",
            }
            var putPolicy = new qiniu.rs.PutPolicy(options);

            //token
            var uploadToken = putPolicy.uploadToken(mac);
            var config = new qiniu.conf.Config();
            // // 空间对应的机房
            // config.zone = qiniu.zone.Zone_z0;
            //构建上传
            var formUploader = new qiniu.form_up.FormUploader(config);

            var putExtra = new qiniu.form_up.PutExtra();
            //文件名
            var d = new Date();
            var guid = 1000;
            var name = [d.getFullYear(), d.getMonth() + 1, d.getDate(), d.getHours(),
                    d.getMinutes(), d.getSeconds(), d.getMilliseconds(), guid++]
                    .join('_') + path.extname(req.files.file.path);
            var key = name;
            var localFile = req.files.file.path;
            // 文件上传
            formUploader.putFile(uploadToken, key, localFile, putExtra, function (respErr,
                                                                                  respBody, respInfo) {
                console.log(respErr);
                console.log(respInfo);
                if (respErr) {
                    throw respErr;
                }
                if (respInfo.statusCode == 200) {
                    var r = {
                        'url': 'https://img.darongshutech.com/' + respBody.key,
                        'original': name,
                        'state': 'SUCCESS'
                    };
                    res.json(r);
                } else {
                    console.log(respInfo.statusCode);
                    console.log(respBody);
                }
            });
        } else {
            var url = API.uploads;
            var options = {
                url: url,
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            }; 
            var formData = {file: fs.createReadStream(req.files.file.path)};
            options.formData = formData; 
            request.post(options, function (err, httpResponse, body) {
                console.log('回调函数', JSON.parse(body));
                if (err) {
                    var r = {
                        'original': d.getFullYear() + '.png',
                        'state': '上传失败'
                    }; 
                    res.json(r);
                }
                var date = JSON.parse(body);
                var d = new Date();
                var guid = 1000;
                var name = [d.getFullYear(), d.getMonth() + 1, d.getDate(), d.getHours(),
                        d.getMinutes(), d.getSeconds(), d.getMilliseconds(), guid++]
                        .join('_') + path.extname(req.files.file.path);
                        console.log(name);
                var r = {
                    'url': date.data.base_url + date.data.key,
                    'original': name,
                    'state': 'SUCCESS'
                }; 
                res.json(r);
            });
        }
    });

    //获取视频token
    router.get('/getToken', function (req, res, next) {
        var data = {};
        Http({url: API.getToken, method: 'GET'}, data, function (content) {
                res.json(content);
        }, req);
    });  
};

module.exports = module_upload;
