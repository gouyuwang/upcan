<?php
/*
逻辑层基类
*/

namespace App\Http\Controllers\Pub\V1\Logic;
use App\Http\Controllers\Logic;
class BaseLogic extends Logic
{
    public $errorMsg = [
        4000 => '参数请求错误!',
        4001 => 'json解析出错!',
        4099 => '系统繁忙,请稍后重试!',
    ];

    //错误返回
    public function _Error($code = 4000, $data = [], $msg = false)
    {
        if (!array_key_exists($code, $this->errorMsg) || !isset($code)) {
            $code = 4099;
        }
        $res = [
            'code' => $code,
            'msg' => $msg === false ? $this->errorMsg[$code] : $msg,
        ];
        if (!empty($data)) {
            $res['data'] = $data;
        }
        return $res;
    }

    //正确返回
    public function _Success($data = [], $msg = '请求成功')
    {
        $res = [
            'code' => 2000,
            'msg' => $msg,
        ];
        if (!empty($data)) {
            $res['data'] = $data;
        }
        return $res;
    }

}
