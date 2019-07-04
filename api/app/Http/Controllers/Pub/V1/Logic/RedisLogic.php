<?php

namespace App\Http\Controllers\Pub\V1\Logic;

use App\Extend\Redis;
use App\Models\AreaModel;
use App\Models\AttrModel;

class RedisLogic extends Redis
{
    // 短信验证码
    public function smsCaptcha($telphone, $captcha = false)
    {
        $key = 'sms_captcha_' . $telphone;
        // 存储
        if ($captcha) {
            $rst = $this->set($key, $captcha, 5 * 60);
        } // 获取
        else {
            $rst = $this->get($key);
        }
        return $rst;
    }

    /**
     * TODO 获取分类
     * @param string $attrGroupId
     * @return array|string
     */
    public function attr($attrGroupId)
    {
        $key = 'attr_group_' . $attrGroupId;
        $rst = $this->get($key);
        if (empty($rst)) {
            $params = [
                'where' => ['dele' => 1, 'status' => 1, 'attr_group_id' => $attrGroupId], // 未删除， 且在使用中
                'field' => ['attr_id', 'attr_value', 'pid'],// 查询的字段
                'order' => [['pid', 'asc'], ['sort', 'asc'], ['attr_id', 'asc']],// 排序
            ];
            $rst = (new AttrModel())->selectInfo($params, false);
            $data = [];
            foreach ($rst as $v) {
                $data[$v['pid']][$v['attr_id']] = $v['attr_value'];
            }
            $this->set($key, $data);
            $rst = $data;
        }
        return $rst;
    }

    /**
     * TODO 获取分组
     * @param $attrGroupId
     * @return array|string
     */
    public function attr_group($attrGroupId)
    {
        $key = 'attr_group_sub_' . $attrGroupId;
        $rst = $this->get($key);
        if (empty($rst)) {
            $params = [
                'where' => ['dele' => 1, 'status' => 1, 'attr_group_id' => $attrGroupId], // 未删除， 且在使用中
                'field' => ['attr_id', 'attr_value'],// 查询的字段
                'order' => [['pid', 'asc'], ['sort', 'asc'], ['attr_id', 'asc']],// 排序
            ];
            $rst = (new AttrModel())->selectInfo($params, false);
            $data = [];
            foreach ($rst as $v) {
                $data[$v['attr_id']] = $v['attr_value'];
            }
            $this->set($key, $data);
            $rst = $data;
        }
        return $rst;
    }

    //缓存 区域
    public function area($upper_region)
    {
        $key = 'area_' . $upper_region;
        $data = $this->get($key);
        if (empty($data)) {
            $params = [
                'where' => ['upper_region' => $upper_region, 'use_state' => 1],
                'field' => ['region_name_c', 'id']
            ];
            $res = (new AreaModel())->selectInfo($params, false);
            $obj = [];
            foreach ($res as $key => $value) {
                $obj[$value['id']] = $value['region_name_c'];
            }
            $this->set($key, $obj);
            $data = $obj;
        }
        return $data;
    }

}