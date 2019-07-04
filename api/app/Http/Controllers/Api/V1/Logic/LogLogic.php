<?php
/*
 *  会话逻辑
 */

namespace App\Http\Controllers\Api\V1\Logic;

use App\Models\AttrGroupModel;
use App\Models\AttrModel;
use App\Models\LogModel;
use \App\Http\Controllers\Pub\V1\Logic\RedisLogic;

class LogLogic extends BaseLogic
{
    private $model;

    function __construct()
    {
        $this->model = new LogModel();
    }

    /**
     * TODO 获取日志列表
     * @param array $data
     * @return array
     */
    public function getLogList(array $data = [])
    {
        // 当前页码(非必要)
        $current_page = isset($data['current_page']) ? (int)$data['current_page'] : 1;
        // 页面大小(非必要)
        $page_size = isset($data['page_size']) ? (int)$data['page_size'] : (int)ENV('PAGE_SIZE', 50);
        // 获取参数
        $params = [
            'where' => ['dele' => 1], // 删除
            'limit' => [($current_page - 1) * $page_size, $page_size],
            'groupBy' => 'openid',
            'orderBy' => ['log_time', 'DESC'],
        ];

        isset($data['attr_id']) && $params['where']['attr_id'] = $data['attr_id'];
        !empty($data['q']) && $params['like'] = ['log_content', $data['q']];
        !empty($data['qn']) && $params['like'] = ['nickname', $data['qn']];

        if (!empty($data['d'])) {
            $params['expMore'] = [
                ['log_time', '>=', date('Y-m-d H:i:s', strtotime($data['d']))],
                ['log_time', '<', date('Y-m-d H:i:s', strtotime($data['d']) + 86400)],
            ];
        }


        $rst = $this->model->selectInfo($params);
        $total = 0;

        // 数据处理
        if (!empty($rst)) {
            $total = $this->model->total($params);
//            $cache = (new RedisLogic())->attr_group(AttrGroupModel::ATTR_GROUP_LOG);
            $cache = $this->attr_group(AttrGroupModel::ATTR_GROUP_LOG);
            foreach ($rst as &$v) {
                $attrId = $v['attr_id'];
                if (array_key_exists($attrId, $cache)) {
                    $v['attr_value'] = $cache[$attrId];
                } else {
                    $v['attr_value'] = '/';
                }
            }
        }

        // 返回数据
        return [
            'current_page' => $current_page,
            'page_size' => $page_size,
            'total_num' => $total,
            'list' => $rst
        ];
    }

    /**
     *  TODO  获取日志详情
     * @param array $data
     * @return array
     */
    public function getLogDetails(array $data = [])
    {
        // 当前页码(非必要)
        $current_page = isset($data['current_page']) ? (int)$data['current_page'] : 1;
        // 页面大小(非必要)
        $page_size = isset($data['page_size']) ? (int)$data['page_size'] : (int)ENV('PAGE_SIZE', 50);
        // 获取参数
        $params = [
            'where' => ['dele' => 1, 'openid' => $data['openid']],
            'limit' => [($current_page - 1) * $page_size, $page_size],
            'orderBy' => ['log_time', 'DESC'],
        ];

        isset($data['attr_id']) && $params['where']['attr_id'] = $data['attr_id'];
        !empty($data['q']) && $params['like'] = ['log_content', $data['q']];
        !empty($data['qn']) && $params['like'] = ['nickname', $data['qn']];

        if (!empty($data['d'])) {
            $params['expMore'] = [
                ['log_time', '>=', date('Y-m-d H:i:s', strtotime($data['d']))],
                ['log_time', '<', date('Y-m-d H:i:s', strtotime($data['d']) + 86400)],
            ];
        }

        $rst = $this->model->selectInfo($params);
        $total = 0;
        // 数据处理
        if (!empty($rst)) {
            $total = $this->model->total($params);
            // $cache = (new RedisLogic())->attr_group(AttrGroupModel::ATTR_GROUP_LOG);
            $cache = $this->attr_group(AttrGroupModel::ATTR_GROUP_LOG);
            foreach ($rst as &$v) {
                $attrId = $v['attr_id'];
                if (array_key_exists($attrId, $cache)) {
                    $v['attr_value'] = $cache[$attrId];
                } else {
                    $v['attr_value'] = '/';
                }
            }
        }

        // 返回数据
        return [
            'current_page' => $current_page,
            'page_size' => $page_size,
            'total_num' => $total,
            'list' => $rst
        ];
    }


    /**
     * TODO 删除日志
     * @param array $data
     * @return bool|int
     */
    public function deleLog(array $data = [])
    {
        $params = [
            'where' => [
                'log_id' => $data['log_id'] // 日志ID
            ],
        ];
        $rst = $this->model->deleInfo($params);
        return $rst;
    }

    /**
     * TODO 设置日志分类
     * @param array $data
     */
    public function setLogAttr(array $data = [])
    {
        $params = [
            'where' => ['log_id' => $data['log_id']],
            'data' => [
                'attr_id' => $data['attr_id']
            ],
        ];
        return $this->model->editInfo($params);
    }

    /**
     * 获取attr
     * @param null $attrGroupId
     * @return array
     */
    public function attr_group($attrGroupId = null)
    {
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
        return $data;
    }
}
