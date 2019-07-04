//验证权限
var auth = function(req,res,next){ 
	if (req.session.user) {
		next();
	}else{
		res.redirect('/login');
	}
};

module.exports = auth;
