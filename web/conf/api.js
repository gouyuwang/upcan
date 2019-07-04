var CONF = require('../conf/conf.js');
var base = CONF.HOSTNAME; 
var base_api = base + '/api';//管理端
var base_public = base + '/public';//管理端
var API = {};

// 权限管理
API.login = base_api + '/login';

// 业务相关
// 分类
API.attr = base_api+ '/attr';
// 日志
API.log = base_api + '/log';
// 媒体
API.media = base_api + '/media';
// 常用语
API.general = base_api + '/general';
// 自动回复
API.auto = base_api + '/auto';
// 管理员
API.admin = base_api + '/admin';
API.adminState = base_api + '/adminState';


// 直传获取token
API.getToken = base_public + '/getToken';
//图片上传
API.uploads = base_public + '/uploads';

module.exports = API;
