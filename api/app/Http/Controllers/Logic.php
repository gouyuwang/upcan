<?php

namespace App\Http\Controllers;

use App\Extend\JWT;
use App\Extend\Redis;
use App\Extend\Tool;
use App\Http\Controllers\Pub\V1\Logic\RedisLogic;

class Logic
{

    //数据解析函数
    public function getData($request)
    {
        $data = $request->all();
        return $data;
    }

    //获取客户端IP
    public function get_real_ip()
    {
        $ip = false;
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    //获取token
    protected function getToken($arr = [])
    {
        $key = ENV('JWT_SECRET');
        $jwt_ttl = time() + ENV('JWT_TTL');
        $arr['exp'] = $jwt_ttl;
        return JWT::encode($arr, $key);
    }


    /**
     * TODO 验证是否是手机号
     * @param $str
     * @return int
     */
    protected function isPhone($str)
    {
        return preg_match('/^13\d{9}$|^14\d{9}$|^15\d{9}$|^17\d{9}$|^18\d{9}$/', $str);
    }

    /**
     * TODO 将经纬度映射到区域表结构
     * @param $lat
     * @param $lng
     * @return array|bool
     */
    public function  reverseLatLng2LocalAreaMap($lat, $lng)
    {
        // 获取解析数据
        $data = Tool::renderAddressReverse($lat, $lng);
        if ($data != false) {
            $cache = new RedisLogic();
            $provinceMap = $cache->area(0);// 获取省
            $provinceName = $data['province'];
            if (false !== $provinceName) {
                $provinceId = array_search($provinceName, $provinceMap);
                if (false !== $provinceId) {
                    $cityMap = $cache->area($provinceId); // 获取市
                    $cityName = $data['city'];
                    if (false !== $cityName) {
                        $cityId = array_search($cityName, $cityMap);
                        if (false !== $cityId) {
                            $areaMap = $cache->area($cityId);// 获取区
                            $areaName = $data['area'];
                            if (false !== $areaName) {
                                $areaId = array_search($areaName, $areaMap);
                                // 返回结果
                                return [
                                    'province_id' => $provinceId,
                                    'city_id' => $cityId,
                                    'area_id' => $areaId == false ? null : $areaId,
                                ];
                            }
                        }
                    }
                }
            }
        }
        return [];
    }

    /**
     * TODO 获取格式化字符串输出
     * @param $url
     * @param int $type 默认图片上传
     * @return string
     */
    public function formatUrl($url, $type = 1)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            switch ((int)$type) {
                case 2:
                    $url = ENV('WEB_H5_HOST') . $url;
                    break;
                default:
                    $url = ENV('WEB_UPLOAD_HOST') . $url;
                    break;
            }
        }
        return $url;
    }


    /**
     * TODO 格式化课程标签
     * @param $tag
     * @return array
     */
    public function formatTag($tag)
    {
        return explode('|', $tag);
    }

}
