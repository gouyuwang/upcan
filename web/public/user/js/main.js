/**
 * Http请求
 * @params req 请求
 * @params params 请求数据
 * @params cb 请求回调
 * @params tips 提示配置 {title:'',style:''}
 **/
if (!window.Http) {
    window.Http = function(req, params, cb, tips) {
       layui.use(['layer'], function(){
        var layer = layui.layer;
        var loading;
        var defaultStyle = { title: '请稍后...', style: { icon: 16 } };
        if (tips) {
            tips.title = tips.title || defaultStyle.title;
            tips.style = $.extend(defaultStyle.style, tips.style || {});
        } else {
            tips = defaultStyle;
        }

        $.ajax({
            type: req.method,
            url: req.url,
            data: params,
            beforeSend: function() {
                loading = layer.msg(tips.title, tips.style);
            },
            success: function(e) {
                typeof(cb) == 'function' && cb(e);
            },
            error: function(e) {
                layer.msg('请求出错:' + JSON.stringify(e), { icon: 2, time: 5000, offset: '10px' });
            },
            complete: function(e) {
                layer.close(loading);
            }
        });
      });
    }
}

/**
 * 在没有原生支持localStorage对象的浏览器中使用它
 **/
if (!window.localStorage) {
    window.localStorage = {
        getItem: function(sKey) {
            if (!sKey || !this.hasOwnProperty(sKey)) { return null; }
            return unescape(document.cookie.replace(new RegExp("(?:^|.*;\\s*)" + escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*"), "$1"));
        },
        key: function(nKeyId) { return unescape(document.cookie.replace(/\s*\=(?:.(?!;))*$/, "").split(/\s*\=(?:[^;](?!;))*[^;]?;\s*/)[nKeyId]); },
        setItem: function(sKey, sValue) {
            if (!sKey) { return; }
            document.cookie = escape(sKey) + "=" + escape(sValue) + "; path=/";
            this.length = document.cookie.match(/\=/g).length;
        },
        length: 0,
        removeItem: function(sKey) {
            if (!sKey || !this.hasOwnProperty(sKey)) { return; }
            var sExpDate = new Date();
            sExpDate.setDate(sExpDate.getDate() - 1);
            document.cookie = escape(sKey) + "=; expires=" + sExpDate.toGMTString() + "; path=/";
            this.length--;
        },
        hasOwnProperty: function(sKey) { return (new RegExp("(?:^|;\\s*)" + escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie); }
    };
    window.localStorage.length = (document.cookie.match(/\=/g) || window.localStorage).length;
}

/**
 * 将表单参数转换成对象
 **/
jQuery.prototype.serializeObject = function() {
    var obj = new Object();
    $.each(this.serializeArray(), function(index, param) {
        if (!(param.name in obj)) {
            obj[param.name] = param.value;
        }
    });
    return obj;
};
 

/**
 * URL校验
 */
if (!window.isUrl){
   window.isUrl =  function(url){
        var regx =/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
        var objExp = new RegExp(regx);
        return objExp.test( url );
   }
}

  /**
   *   分页
   * @params total 总共条数
   * @params curr_page 当前页码
   * @params pageSize 页面大小
   * @params call 回调分页函数
   **/
if(!window.multi){  
  window.multi = function ( total, curr_page, pageSize, call) {
        var multipage = '';
        multipage += '<ul class="pagination">'
        if ( total > pageSize) {
          var page = 5;  // 数字页面个数
          var offset = 2; // 偏移
          var pages = Math.ceil ( total / pageSize ); // 总页数
          var from = curr_page - offset;
          var to = curr_page + page - offset - 1;
          if ( page > pages) {
              from = 1;
              to = pages;
          } else {
              if (from < 1) {
                  to = curr_page + 1 - from;
                  from = 1;
                  if ((to - from) < page && (to - from) < pages) {
                      to = page;
                  }
              } else if ( to > pages) {
                  from = curr_page - pages + to;
                  to = pages;
                  if ((to - from) < page && ( to - from) < pages) {
                      from = pages - page + 1;
                  }
              }
          } 

          //上一页
          if ( curr_page > 1 && pages >= curr_page) {
              multipage += '<li><a href="javascript:'+call+'('+(curr_page-1)+','+pageSize+')">«</a></li>';
          } else { // disabled="disabled"
               multipage += '<li class="disabled"><span>«</span></li>';
          }

          //数字页码
          for(var i = from; i <= to; i ++) {
              if (i != curr_page) {
                   multipage += '<li><a href="javascript:'+call+'('+i+','+pageSize+')"><span>'+i+'</span></a></li>';
              } else {  //当前页   disabled="disabled"
                   multipage += '<li class="active"><span>'+i+'</span></li>';
              }
          }

          //下一页
          if (curr_page > 0 && curr_page < pages) {
              multipage += '<li><a href="javascript:'+call+'('+(curr_page+1)+','+pageSize+')"><span>»</span></a></li>';
          } else {
              multipage += '<li class="disabled"><span>»</span></li>';
          } 

       }
       multipage += '</ul>';
       return multipage;
  }
}