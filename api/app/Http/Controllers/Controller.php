<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Extend\JWT;

class Controller extends BaseController
{
    //初始化函数
    public function __construct()
    {

    }

    //请求返回的值    $request请求对象,$obj返回数据
    public function jsonData($request, $obj)
    {
        $data = [];
        if ($request->isMethod('get')) {
            if (is_array($obj)) {
                $data = [
                    'code' => 2000,
                    'msg' => '请求成功',
                    'data' => $obj
                ];
            } else {
                $data = [
                    'code' => 4000,
                    'msg' => '请求失败'
                ];
            }
        } else {
            if (is_array($obj)) {
                $data = [
                    'code' => 4000,
                    'msg' => empty($obj['msg']) ? '操作失败' : $obj['msg']];
            } else {
                $data = [
                    'code' => 2000,
                    'msg' => '操作成功'
                ];
            }
        }
        return $this->toJson($data);
    }

    //直接输出
    public function toJson($data)
    {
        if (array_key_exists('code', $data)) {
            is_string($data['code']) ? $data['code'] = (int)$data['code'] : $data['code'];
        }
        $data = $this->filterData($data);
        return $this->encrypt($data);
    }

    //数据解析函数
    public function getData($request)
    {
        $key = ENV('JWT_SECRET');//jwt_key
        $all = $request->all();

        // //判断get请求和其他请求
        $obj = isset($all['token']) ? JWT::decode($all['token'], $key, array('HS256')) : 'token不得为空';
        if (is_object($obj)) {
            isset($obj->{'user_id'}) && $all['user_id'] = $obj->{'user_id'};
            unset($all['token']);
        }
        if (isset($all['debug'])) {
            print_r($all);
            exit;
        }
        return $all;
    }


    //清除数组中的null字段
    private function filterData($arr = [])
    {
        return $arr;
    }

    //数据加密
    public function encrypt($data)
    {
        return $data;
    }
}
