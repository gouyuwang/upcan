<?php
/**
 *  服务器控制逻辑
 */

namespace App\Http\Controllers\Api\V1\Logic;

use App\Extend\Tool;
use App\Models\AutoModel;
use App\Models\LogModel;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Transfer;


class ServerLogic extends BaseLogic
{
    private $app;

    function __construct()
    {
        $options = [
            'debug' => true,

            'app_id' => 'wx8f2ec1e224f2c0df',
            'secret' => '29fe9d23bd9fa28ca772f8cdc4f27b4d',
            'token' => '1559143',
            'aes_key' => 'rTIQy52PIoTS6jUUR5ZeP7rtUH9rxCcfqwGBgYLTyld',

            'log' => [
                'level' => 'debug',
                'file' => storage_path('logs/wechat-server-product.log'),
            ],
        ];
        $this->app = new Application($options);
    }

    /**
     * TODO 微信服务逻辑
     * @param array $data
     * @return $mixed
     */
    public function server(array $data = [])
    {
        $server = $this->app->server;
        $user = $this->app->user;
        $server->setMessageHandler(function ($message) use ($user) {
            $msgType = strtolower($message->MsgType);
            $fromUser = $user->get($message->FromUserName);
            switch ($msgType) {
                case "event":
                    switch ($message->Event) {
                        case 'subscribe':
                            return "您好,感谢您关注大能机器人官方公众号。";
                            break;
                        default:
                            break;
                    }
                    break;
                case "text":
                    // 记录问题
                    (new LogModel())->addInfo([
                        'log_id' => $message->MsgId,
                        'log_content' => '[TEXT]' . $message->Content,
                        'log_time' => date('Y-m-d H:i:s',time()),
                        'openid' => $fromUser->openid,
                        'nickname' => $fromUser->nickname,
                        'headimgurl' => $fromUser->headimgurl,
                        'type' => 'text',
                    ]);
                    // 自动回复
                    return $this->autoReply($message->Content, $message->FromUserName);
                    break;
                case "voice":
                case "video":
                    (new LogModel())->addInfo([
                        'log_id' => $message->MsgId,
                        'log_content' => '[VIDEO]' . $message->MediaId,
                        'log_time' => date('Y-m-d H:i:s',time()),
                        'openid' => $fromUser->openid,
                        'nickname' => $fromUser->nickname,
                        'headimgurl' => $fromUser->headimgurl,
                        'type' => 'video',
                    ]);
                    // 自动回复
                    return $this->autoReply('', $message->FromUserName);
                    break;
                case "location":
                    (new LogModel())->addInfo([
                        'log_id' => $message->MsgId,
                        'log_content' => '[LOCATION]' . $message->Label,
                        'log_time' => date('Y-m-d H:i:s',time()),
                        'openid' => $fromUser->openid,
                        'nickname' => $fromUser->nickname,
                        'headimgurl' => $fromUser->headimgurl,
                        'type' => 'location',
                    ]);
                    // 自动回复
                    return $this->autoReply('', $message->FromUserName);
                    break;
                case "image":
                    (new LogModel())->addInfo([
                        'log_id' => $message->MsgId,
                        'log_content' => "[IMAGE]" . $message->PicUrl,
                        'log_time' => date('Y-m-d H:i:s',time()),
                        'openid' => $fromUser->openid,
                        'nickname' => $fromUser->nickname,
                        'headimgurl' => $fromUser->headimgurl,
                        'type' => 'image',
                    ]);
                    // 自动回复
                    return $this->autoReply('', $message->FromUserName);
                    break;
                default:
                    // 自动回复
                    return $this->autoReply('', $message->FromUserName);
                    break;
            }
            return '';
        });
        return $server->serve()->send();
    }

    /**
     * TODO 获取素材列表
     * @param array $data
     * @return array
     */
    public function getMaterial(array $data = [])
    {
        $material = $this->app->material;
        $lists = $material->lists($data['type'], 0, 100);
        return $this->_Success($lists);
    }


    /**
     * TODO 自动回复
     * @param $text
     */
    public function autoReply($text = '', $openId = '')
    {
        // 获取所有关键词
        $params = [
            'where' => [
                'dele' => 1,
            ],
            'field' => [
                'auto_words',
                'auto_replay'
            ]
        ];
        $replyRules = (new AutoModel())->selectInfo($params, false);
        // 通过关键字搜索是否存在自定义回复内容
        foreach ($replyRules as $v) {
            if (false !== stripos($text, $v['auto_words'])) {
                // 如果存在则直接回复
                return $v['auto_replay'];
            }
        }
        // 如果不存在，则上班时间通知人工回复，下班时间直接让他等待
        date_default_timezone_set('Asia/Shanghai');
        $h = date('G');
        if ($h > 17 || $h < 9) {
            $msg = "您咨询的问题已收到, 由于当前是非工作时间， 客服可能无法及时回复。客服工作时间：早上9点 - 晚上6点";
        } else {
            $msg = "您好，正在为您接入在线人工客服，请稍后。";
            $this->app->staff->message($msg)->to($openId)->send();
            return new Transfer(); // 消息转发
        }
        return $msg;
    }


    /**
     * TODO 获取客服咨询记录
     * @param array $data
     * @return mixed
     */
    public function getStaffLog($data = [])
    {
        $staff = $this->app->staff;
        // 当前页码(非必要)
        $current_page = isset($data['current_page']) ? (int)$data['current_page'] : 1;
        // 页面大小(非必要)
        $page_size = isset($data['page_size']) ? (int)$data['page_size'] : (int)ENV('PAGE_SIZE', 20);
        // 查询开始时间和结束时间 时间跨度不超过一天
        $beginDate = empty($data['begin_date']) ? date('Y-m-d', time()) : date('Y-m-d', strtotime($data['begin_date']));
        $endDate = empty($data['end_date']) ? date('Y-m-d', strtotime('+1 days')) : date('Y-m-d', strtotime($data['end_date']));
        // 记录获取
        $records = $staff->records($beginDate, $endDate, $current_page, $page_size);
        return $records;
    }


    /**
     * TODO 备份日志到数据库中
     * @param array $data
     * @return mixed
     */
    public function backupLog2db($data = [])
    {
        $max = 100;
        $i = 1;
        $count = 0;
        $save_count = 0;
        $err_count = 0;
        while ($max > $i) {
            // 获取参数设置
            $params = [
                'current_page' => $i++,
                'page_size' => 20,
                'begin_date' => empty($data['begin_date']) ? date('Y-m-d', strtotime('-1 days')) : date('Y-m-d', strtotime($data['begin_date'])),
                'end_date' => empty($data['end_date']) ? date('Y-m-d', time()) : date('Y-m-d', strtotime($data['end_date'])),
            ];
            $records = $this->getStaffLog($params);
            if (empty($records->recordlist)) {
                $content = "合计记录条数：{$count}, 存储个数：{$save_count}, 失败记录：{$err_count}";
                Tool::addLog([
                    'name' => 'wechat-log',  // 日志名称
                    'tip' => 'wechat-log',   // 日志标识
                    'data' => ['params' => $params, 'content' => $content]  //  日志的内容数组
                ]);
                return $this->_Success([$content]);
            } else {
                $content = "合计记录条数：0, 存储个数：0, 失败记录：0";
                Tool::addLog([
                    'name' => 'wechat-log',  // 日志名称
                    'tip' => 'wechat-log',   // 日志标识
                    'data' => ['params' => $params, 'content' => $content]  //  日志的内容数组
                ]);
            }

            $user = $this->app->user;
            foreach ($records->recordlist as $v) {
                $count++;
                $fromUser = $user->get($v['openid']);
                $rst = (new LogModel())->addInfo([
                    'log_id' => $v['time'] . rand_string(9, 1),
                    'log_content' => '[TEXT]' . $v['text'],
                    'log_time' => date('Y-m-d H:i:s', (int)$v['time']),
                    'openid' => $v['openid'],
                    'nickname' => $fromUser->nickname,
                    'headimgurl' => $fromUser->headimgurl,
                    'type' => 'text',
                    'opercode' => $v['opercode'],
                    'worker' => $v['worker'],
                ]);
                if (is_array($rst)) {
                    $err_count++;
                } else {
                    $save_count++;
                }
            }
        }
        return !0;
    }

    /**
     * TODO 菜单设置
     * @param array $data
     * @return array
     */
    public function setMenu(array $data = [])
    {
        $menu = $this->app->menu;
        $buttons = [
            [
                "type" => "miniprogram",
                "name" => "进入商城",
                "url" => "https://mp.weixin.qq.com/a/~Bpp4vpIPx684t_zjsku3kg~~",
                "appid" => "wxbf88b99b15a58cea",
                "pagepath" => "pages/page10056/page10056"
            ],
            [
                "type" => "view_limited",
                "name" => "参与活动",
                "media_id" => "Cm6Yhd3zeqZDBx0Okj2itnPeG5OIn9_wWnu_Uodd5vM"
            ],
            [
                "name" => "关于大能",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "APP下载",
                        "url" => "http://www.upcanrobot.com/appxz"
                    ],
                    [
                        "type" => "view",
                        "name" => "在线客服",
                        "url" => "http://www.bangwo8.com/client/smartRobot_phone_v1.php?VendorID=175407&tagName=&weChatType=2"
                    ],
                    [
                        "type" => "view_limited",
                        "name" => "联系我们",
                        "media_id" => "Cm6Yhd3zeqZDBx0Okj2itoejJ8jOpRg-_V3bXVNHV2w"
                    ],
                    [
                        "type" => "view",
                        "name" => "故障报修",
                        "url" => "http://www.bangwo8.com/t.php?MTc1NDA3OjM="
                    ]
                ],
            ],
        ];
        return $menu->add($buttons) ? $this->_Success() : $this->_Error();
    }


}
