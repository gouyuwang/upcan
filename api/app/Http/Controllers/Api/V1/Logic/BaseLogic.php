<?php
/*
逻辑层基类
*/

namespace App\Http\Controllers\Api\V1\Logic;

use App\Extend\JWT;
use App\Http\Controllers\Logic;

class BaseLogic extends Logic
{
    /**
     * TODO 错误标记
     * @var array
     */
    public $errorMsg = [
        4000 => '参数请求错误!',
        4001 => '获取手机号码失败!请重试!',
        4002 => '该手机号已经注册!',
        4003 => 'token过期!',
        4004 => '输入验证码有误!',
        4005 => '请求超时!',
        4006 => '获取session_key参数错误!',
        4007 => '数据包含非法字符!',
        4008 => '当前报名人数已满!',
        4009 => '系统响应超时!请稍后请求!',
        4010 => '你已经报名了!',
        4011 => '请先登录!',
        4012 => '数据解密失败!',
        4013 => '你已经评价过了!',
        4014 => '该数据已失效!',
        4015 => '重复修改!',
        4016 => '!',
        4017 => '!',
        4018 => '!',
        4019 => '!',
        4020 => '没有可以推送的消息!',
        4099 => '系统繁忙,请稍后重试!',
    ];

    /**
     * TODO 错误返回
     * @param int $code
     * @param array $data
     * @param bool $msg
     * @return array
     */
    public function _Error($code = 4000, $data = [], $msg = false)
    {
        if (!array_key_exists($code, $this->errorMsg)
            || !isset($code)
        ) {
            $code = 4099;
        }
        $res = [
            'code' => $code,
            'msg' => ($msg === false ? $this->errorMsg[$code] : $msg)
        ];
        if (!empty($data)) {
            $res['data'] = $data;
        }
        return $res;
    }

    /**
     * TODO 正确返回
     * @param array $data
     * @param string $msg
     * @return array
     */
    public function _Success($data = [], $msg = '请求成功')
    {
        $res = [
            'code' => 2000,
            'msg' => $msg
        ];
        if (!empty($data)) {
            $res['data'] = $data;
        }
        return $res;
    }

    /**
     * TODO 调试数据
     * @param $data
     */
    public function __Debug($data)
    {
        print_r(__checkVar($data));
        exit;
    }

    /**
     * TODO 生成token
     * @param array $arr
     * @return string
     */
    protected function getToken($arr = [])
    {
        $key = ENV('JWT_SECRET');
        $jwt_ttl = time() + ENV('JWT_TTL');
        $arr['exp'] = $jwt_ttl;
        return JWT::encode($arr, $key);
    }
}
