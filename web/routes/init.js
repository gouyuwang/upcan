var express = require('express');
var router = express.Router();
// 首页
require('./index')(router);
// 上传
require('./upload')(router);
// 通用部分
require('./comm')(router);
module.exports = router;