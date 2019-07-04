var express = require('express');
var path = require('path');
var favicon = require('serve-favicon');
var logger = require('morgan');
var cookieParser = require('cookie-parser');
var bodyParser = require('body-parser');
var session = require('express-session');
var ueditor = require('ueditor-nodejs');
//路由
var routes = require('./routes/init');

var swig = require('swig');

var app = express();

// view engine setup
//app.set('views', path.join(__dirname, 'views'));
//app.set('view engine', 'jade');
app.engine('html', swig.renderFile);

app.set('view engine', 'html');
app.set('views', __dirname + '/views');

app.use(bodyParser.json({limit: '20mb'})); // for parsing application/json
app.use(bodyParser.urlencoded({limit: '20mb', extended: true })); // for parsing application/x-www-form-urlencoded


//富文本上传
app.use('/lib/ueditor/1.4.3/ue',ueditor({//这里的/ueditor/ue是因为文件件重命名为了ueditor,如果没改名，那么应该是/ueditor版本号/ue
    configFile: '/lib/ueditor/1.4.3/php/config.json',//如果下载的是jsp的，就填写/ueditor/jsp/config.json
    mode: 'bcs', //本地存储填写local
    dynamicPath: '/rmrbpublic' //动态目录，以/开头，bcs填写buckect名字，开头没有/.路径可以根据req动态变化，可以是一个函数，function(req) { return '/xx'} req.query.action是请求的行为，uploadimage表示上传图片，具体查看config.json.
}));

// uncomment after placing your favicon in /public
//app.use(favicon(path.join(__dirname, 'public', 'favicon.ico')));
app.use(logger('dev'));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
app.use(cookieParser());
app.use(session({
  secret: 'dfddfdfdfdd@ggkk#%',
  cookie:{
    maxAge: 1000*60*60*24,
  },
  resave: false,
  saveUninitialized: true
}));

app.use(express.static(path.join(__dirname, 'public')));

app.use('/', routes);//内容管理
// app.use('/users', users);//功能管理
// catch 404 and forward to error handler
app.use(function(req, res, next) {
  var err = new Error('Not Found');
  err.status = 404;
  next(err);
});

// error handlers

// development error handler
// will print stacktrace
if (app.get('env') === 'development') {
  app.use(function(err, req, res, next) {
    res.status(err.status || 500);
    res.render('error', {
      message: err.message,
      error: err
    });
  });
}

// production error handler
// no stacktraces leaked to user
app.use(function(err, req, res, next) {
  res.status(err.status || 500);
  res.render('error', {
    message: err.message,
    error: {}
  });
});


module.exports = app;
