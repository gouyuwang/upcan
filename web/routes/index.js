//配置文件
var CONF = require('../conf/conf.js');
var API = require('../conf/api.js');
var Http = require('../util/http.js');
var Auth = require('../util/auth.js');
var svgCaptcha = require('svg-captcha'); 

var module_index = function (router) {

    //获取视频token
    router.get('/getToken', function (req, res, next) {
        var data = {};
        Http(
            {url: API.getToken, method: 'GET'},
            data,//前端数据
            function (content) {
                res.json(content);
            }, req);
    });

    // 获取验证码
    router.get('/captcha',function(req,res){
          var codeConfig = {
              size: 5,     // 验证码长度
              ignoreChars: '0o1i', // 验证码字符中排除 0o1i
              noise: 2,   // 干扰线条的数量
              height: 44, // 二维码高度
              color:true, // 有颜色
          }
          // var captcha = svgCaptcha.create(codeConfig); // 字母
          var captcha = svgCaptcha.createMathExpr(codeConfig);// 数学表达式
          // 存session用于验证接口获取文字码
          req.session.captcha = captcha.text.toLowerCase();
          res.type('svg');
          res.status(200).send(captcha.data);
    });

    // 登录页
    router.get('/login',function(req, res, next) {
        res.render('login');
    });

    router.post('/login', function(req, res, next) {
        var params = req.body;
        if (params.code && params.code.toLowerCase() == req.session.captcha ) {
          // 前端验证
          if(!params.username){
             res.json({code:4000,msg:'请输入账号'});
          }
          if(!params.password){
             res.json({code:4000,msg:'请输入密码'});
          }
          // 后台验证
          Http({url: API.login, method: 'POST'}, params,
              function (content) {
                  var code = content.code;
                  if (code == 2000) {
                    req.session.user = content.data;
                  } 
                  res.json(content);
            }, req,res); 

        } else { 
          res.json({code:4000,msg:'验证码出错'});
        }   
    });

    // 用户退出
    router.get('/logout',function(req, res, next) {
        delete req.session.user;
        res.redirect('/login');
    });

    //首页
    router.get('/', Auth,function(req, res, next) {
      console.log(req.session.user);
        res.render('index',req.session.user);
    }); 

    // 首页
    router.get('/index',Auth, function(req, res, next) {
         console.log(req.session.user);
         res.render('index',req.session.user);
    });

    // 分类
    router.get('/logAttr',Auth, function(req, res, next) {
        var params = req.query;
        params.page_size = 9999;
        console.log(params);
        Http({url:API.attr+'/'+1,method:'GET'},params, function(content){
            console.log(content);
            res.json( content.data || {} );
        },req,res);
    });


    // 欢迎页
    router.get('/welcome', Auth,function(req, res, next) {
        res.render('welcome');
    });


    // 视频管理
    router.get('/video-list',Auth,function(req,res,next){
        var params = req.query;
        Http({url:API.media, method:'GET'},params,function(content){
              console.log(content); 
              res.render('video_list',content.data||{});
        },req,res);
    });

    // 视频添加
    router.get('/video-add',Auth,function(req,res,next){
        res.render('video_add');
    });

    // 视频编辑
    router.get('/video-edit',Auth,function(req,res,next){
        var params = req.query;
        console.log(params);
        Http({url:API.media+'/'+params.media_id, method:'GET'},params,
           function(content){
            res.render('video_add', content.data||{});
        },req,res);
    }); 

    // 添加视频
    router.post('/addVideo',function(req,res,next){
        var params = req.body;
        Http({url:API.media, method:'POST'},params,
           function(content){
             res.json(content);
        },req,res);
    });

    // 编辑视频
    router.put('/editVideo',function(req,res,next){
        var params = req.body;
        Http({url:API.media, method:'PUT'},params,
        function(content){
            res.json(content);
        },req,res);
    });
   
    // 删除视频
    router.delete('/deleteVideo',function(req,res,next){
         var params = req.body;
         Http({url:API.media, method:'DELETE'},params,
         function(content){
            res.json(content);
         },req,res);
    });


    // 咨询记录
    router.get('/log-list',Auth,function(req,res,next){
        var params = req.query;
        params.page_size = 20;
        Http({url:API.log, method:'GET'},params,function(content){
          content.data = Object.assign(content.data, params); 
          res.render('log_list',content.data||{});
        },req,res);
    });

    // 咨询详情
    router.get('/log-details',Auth,function(req,res,next){
        var params = req.query||{};  
        Http({url:API.log+'/'+params.openid, method:'GET'},params,function(content){
           content.data = Object.assign(content.data, params); 
           res.render('log_details',content.data||{});
        },req,res);
    });

    // 修改咨询
    router.put('/editLogAttr',function(req,res,next){
        var params = req.body;
        Http({url:API.log,method:'PUT'},params, function(content){
             res.json(content);
        },req,res);
    });

    // 删除咨询
    router.delete('/deleLog',function(req,res,next){
        var params = req.body;
        Http({url:API.log,method:'DELETE'},params, function(content){
             res.json(content);
        },req,res);
    });



    // 分类管理
    router.get('/category-list',Auth,function(req,res,next){
        var params = req.query;
        Http({url:API.attr+'/'+1,method:'GET'},params,
          function(content){
             res.render('category_list',content.data || {});
          },req,res);
    });

    // 添加分类
    router.post('/addAttr',function(req,res,next){
        var params = req.body;
        Http({url:API.attr,method:'POST'},params, function(content){
             res.json(content);
        },req,res);
    });
    
    // 修改分类 
    router.put('/editAttr',function(req,res,next){
        var params = req.body;
        Http({url:API.attr,method:'PUT'},params, function(content){
             res.json(content);
        },req,res);
    });


    // 删除分类
    router.delete('/deleAttr',function(req,res,next){
        var params = req.body;
        Http({url:API.attr,method:'DELETE'},params, function(content){
             res.json(content);
        },req,res);
    });



    // 自动回复
    router.get('/auto-list', Auth,function(req,res,next){
       var params = req.query;
       console.log(params);
       Http({url:API.auto, method:'GET'},params, function(content){
           console.log(content);
           res.render('auto_list',content.data || {});
        },req,res);    
    });
    
    router.get('/auto-edit',Auth, function(req,res,next){
       var params = req.query;
       Http({url:API.auto+'/'+params.auto_id, method:'GET'},params,function(content){
          res.render('auto_add',content.data || {});
       },req,res);
    });

    router.get('/auto-add',Auth, function(req,res,next){
       res.render('auto_add');
    });

    router.put('/editAuto',function(req,res,next){
       var params = req.body;
       Http({url:API.auto, method:'PUT'}, params, function(content){
           res.send(content);
       },req,res);
    });
    
    router.delete('/deleAuto',function(req,res,next){
       var params = req.body;
       Http({url:API.auto, method:'DELETE'}, params, function(content){
           res.send(content);
       },req,res);
    });
    
    router.post('/addAuto',function(req,res,next){
       var params = req.body;
       Http({url:API.auto, method:'POST'}, params, function(content){
           res.send(content);
       },req,res);
    });





    // 常用语设置
    router.get('/use-list',Auth,function(req,res,next){
        var params = req.query;
         Http({url:API.general, method:'GET'},params, function(content){
             res.render('use_list',content.data || {});
         },req,res);
    });

    router.put('/editUse',function(req,res,next){
       var params = req.body;
       Http({url:API.general, method:'PUT'}, params, function(content){
           res.send(content);
       },req,res);
    });
    
    router.delete('/deleUse',function(req,res,next){
       var params = req.body;
       Http({url:API.general, method:'DELETE'}, params, function(content){
           res.send(content);
       },req,res);
    });
    
    router.post('/addUse',function(req,res,next){
       var params = req.body;
       Http({url:API.general, method:'POST'}, params, function(content){
           res.send(content);
       },req,res);
    });




    // 用户管理
    router.get('/admin-list',Auth,function(req,res,next){
        var params = req.query;
         Http({url:API.admin, method:'GET'},params, function(content){
             res.render('admin_list',content.data || {});
         },req,res);
    });
    
    router.get('/admin-add',Auth,function(req,res,next){
       res.render('admin_add');
    });
    
    router.get('/admin-edit',Auth,function(req,res,next){
        var params = req.query;
         Http({url:API.admin+'/'+params.admin_id, method:'GET'},params, function(content){
             content.data = Object.assign(content.data,params);
             res.render('admin_add',content.data || {});
         },req,res);
    });

    router.put('/editAdmin',function(req,res,next){
       var params = req.body;
       Http({url:API.admin, method:'PUT'}, params, function(content){
           res.send(content);
       },req,res);
    });
    
    router.delete('/deleAdmin',function(req,res,next){
       var params = req.body;
       Http({url:API.admin, method:'DELETE'}, params, function(content){
           res.send(content);
       },req,res);
    });
    
    router.post('/addAdmin',function(req,res,next){
       var params = req.body;
       Http({url:API.admin, method:'POST'}, params, function(content){
           res.send(content);
       },req,res);
    });

    router.put('/adminState',function(req,res,next){
       var params = req.body;
       Http({url:API.adminState, method:'PUT'}, params, function(content){
           res.send(content);
       },req,res);
    });
 
};
module.exports = module_index;
