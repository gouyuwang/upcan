<?php

namespace App\Http\Controllers\Pub\V1\Logic;
use Illuminate\Http\Request;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Processing\ImageUrlBuilder;
use Qiniu\Storage\BucketManager;
use App\Models\UploadModel;
//二维码
use Endroid\QrCode\QrCode;
//日志
use App\Extend\Tool;

class QiniuLogic extends BaseLogic
{
    private $app_key = '';
    private $sec_key = '';
    private $bucket = '';
    private $pipline = '';
    private $ext = [
        'image' => ['png', 'jpg', 'jpeg', 'gif', 'bmp'],
        'radio' => ['flv', 'swf', 'mkv', 'avi', 'rm', 'rmvb', 'mpeg', 'mpg', 'ogg', 'ogv', 'mov', 'wmv', 'mp4', 'webm', 'mp3', 'wav', 'mid'],
        'file' => ['apk', 'rar', 'zip', 'tar', 'gz', '7z', 'bz2', 'cab', 'iso', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'md', 'xml']
    ];

    public function __construct()
    {
        $this->app_key = ENV('QINIU_ACCEC_KEY');
        $this->sec_key = ENV('QINIU_SCRET_KEY');
        $this->bucket = ENV('QINIU_BUCKET');
        $this->pipline = ENV('QINIU_PIPLINE');
    }

    //获取token
    public function getToken($data = [])
    {
        $auth = new Auth($this->app_key, $this->sec_key);
        //空间名
        $bucket = $this->bucket;
        // 转码时使用的队列名称
        $pipeline = $this->pipline;
        $key = 'token_' . date('YmdHis') . '_upcan_radio.mp4';
        //要进行转码的转码操作
        $fops = "avthumb/mp4/r/24/vcodec/libx264";
        $find = array('+', '/');
        $replace = array('-', '_');
        $keys = str_replace($find, $replace, base64_encode($bucket . ':' . $key));
        $fops = $fops . '|saveas/' . $keys;
        $policy = array(
            'persistentOps' => $fops,
            'persistentPipeline' => $pipeline
        );

        $uptoken = $auth->uploadToken($bucket, null, 3600, $policy);
        return ['code' => 2000, 'msg' => '操作成功', 'data' => ['token' => $uptoken, 'key' => $key]];
    }


    /**
     * 七牛云图片上传
     **/
    public function upload($request, $data = [])
    {

        $files = $request->file('file');
        if (empty($files))
            return ['code' => 4000, 'msg' => '请选择上传的文件'];
        $ext = strtolower($files->getClientOriginalExtension());
        $flag = false;
        if ($ext) {
            foreach ($this->ext as $key => $types) {
                if (in_array($ext, $types)) {
                    $flag = $key;
                    break;
                }
            }
        }
        switch ($flag) {
            case 'image':
            case 'file':
                $rst = $this->uploadImg($files);
                break;
            case 'radio':
                $rst = $this->radioUpload($files);
                break;
            default:
                $rst = ['code' => 4000, 'msg' => '不允许上传' . $ext . '类文件'];
                break;
        }
        return $rst;
    }

    //图片上传
    public function uploadImg($images)
    {
        $ext = $images->getClientOriginalExtension();
        $auth = new Auth($this->app_key, $this->sec_key);
        //存放空间。。。。分类todo
        $bucket = $this->bucket;
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 要上传文件的本地路径
        $filePath = $images->getPathName();
        // 上传到七牛后保存的文件名
        $head = date('YmdHis');
        $center = 'upcan';
        $key = $head . '_' . $center . '.' . $ext;
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err != null) {
            return ['code' => 4000, 'msg' => '上传失败', 'error' => json_encode($err)];
        } else {
            $ret['upload_type'] = $ext;
            $this->dealimage($ret);
            return [
                'code' => 2000,
                'msg' => '上传成功',
                'data' => [
                    'hash' => $ret['hash'],
                    'key' => $ret['key'],
                    'base_url' => ENV('WEB_UPLOAD_HOST')
                ]
            ];
        }
    }

    /**
     * TODO 视频上传
     * @param $images
     * @return array
     */
    public function radioUpload($images)
    {
        ini_set("memory_limit", "512M");     // 动态配置内存
        set_time_limit(0);                               //取消执行时间
        $ext = $images->getClientOriginalExtension();
        $auth = new Auth($this->app_key, $this->sec_key);
        //空间名
        $bucket = $this->bucket;
        //转码时使用的队列名称
        $pipeline = $this->pipline;
        $key = date('YmdHis') . '_upcan_radio.' . $ext;
        //要进行转码的转码操作
        $fops = "avthumb/mp4/s/640x360/vb/1.25m";
        $policy = array(
            'persistentOps' => $fops,
            'persistentPipeline' => $pipeline
        );
        $uptoken = $auth->uploadToken($bucket, null, 3600, $policy);
        //上传文件的本地路径cc
        $filePath = $images->getPathName();
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($uptoken, null, $filePath);
        if ($err != null) {
            return ['code' => 4000, 'msg' => '上传失败'];
        } else {
            $r = $this->rename($auth, $bucket, $ret['key'], $key);
            if (!$r) {
                return ['code' => 4000, 'msg' => '重命名文件失败'];
            }
            $ret['upload_type'] = $ext;
            $ret['key'] = $key;
            $this->dealimage($ret);

            return [
                'code' => 2000,
                'msg' => '上传成功',
                'data' => [
                    'hash' => $ret['hash'],
                    'key' => $key,
                    'base_url' => ENV('WEB_UPLOAD_HOST')
                ]
            ];
        }
    }

    /**
     * TODO 上传的视频文件重新命名
     * @param $auth
     * @param $bucket
     * @param $ret
     * @param $key
     * @return bool
     */
    public function rename($auth, $bucket, $ret, $key)
    {
        $error = (new BucketManager($auth))->rename($bucket, $ret, $key);
        if (!$error) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * TODO 保存上传记录
     * @param array $arr
     * @return array|bool|int
     */
    public function dealimage($arr = [])
    {
        $data['upload_url'] = $arr['key'];
        $data['upload_hash'] = $arr['hash'];
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['user_ip'] = $this->get_real_ip();
        isset($arr['user_id']) && $data['user_id'] = $arr['user_id'];
        isset($arr['upload_type']) && $data['upload_type'] = $arr['upload_type'];
        $res = (new UploadModel)->addInfo($data);
        return is_array($res) ? false : $res;
    }

    // 二维码
    public function QR($url)
    {
        $qrCode = new QrCode();
        $qrCode
            ->setText($url)
            ->setSize(300)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            ->setLabelFontSize(16)
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        //图片存储地址
        $filePath = base_path('config') . '/qrcode.png';
        $qrCode->save($filePath);
        $res = $this->QRImg($filePath);
        return $res;
    }

    //图片上传
    public function QRImg($filePath)
    {
        $auth = new Auth($this->app_key, $this->sec_key);
        //存放空间。。。。分类todo
        $bucket = $this->bucket;
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 上传到七牛后保存的文件名
        $head = date('YmdHis');
        $center = 'upcan';
        $key = $head . '_' . $center . '.png';
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err != null) {
            return ['code' => 4000, 'msg' => '上传失败'];
        } else {
            return [
                'code' => 2000,
                'msg' => '上传成功',
                'data' => ENV('WEB_UPLOAD_HOST') . $ret['key']
            ];
        }
    }


    // 本地上传图片
    public function uploadFile($filePath)
    {
        $auth = new Auth($this->app_key, $this->sec_key);
        //存放空间。。。。分类todo
        $bucket = $this->bucket;
        // 生成上传 Token
        $token = $auth->uploadToken($bucket);
        // 上传到七牛后保存的文件名
        $head = date('YmdHis');
        $center = 'upcan';
        $key = $head . '_' . $center . '.png';
        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err != null) {
            return false;
        }
        return ENV('WEB_UPLOAD_HOST') . $ret['key'];
    }
}

?>
