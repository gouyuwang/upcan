<?php

namespace App\Http\Controllers\Pub\V1\Logic;

class PublicLogic extends BaseLogic
{

    /**
     * TODO 友好时间格式化
     * @param $time
     * @return false|string
     */
    static function friendlyDate($strTime)
    {
        $now = strtotime(date("Y-m-d H:i:s", time()));
        $show = strtotime($strTime);
        $dur = $now - $show; // diff
        if ($dur < 0) {
            $str = $strTime;
        } else if ($dur < 60) {
            $str = '刚刚';
        } else if ($dur < 3600) {
            $str = floor($dur / 60) . '分钟前';
        } else if ($dur < 86400) {
            $str = floor($dur / 3600) . '小时前';
        } else if ($dur < 259200) {//3天内
            $str = floor($dur / 86400) . '天前';
        } else {
            $str = $strTime;
        }
        return $str;
    }

    /**
     * TODO HTTP GET请求
     * @param $url
     * @return mixed
     */
    public function httpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
