<?php
/*
 *  日志逻辑
 */

namespace App\Http\Controllers\Api\V1\Logic;

use App\Models\AttrModel;

class AttrLogic extends BaseLogic
{
    private $model;

    function __construct()
    {
        $this->model = new AttrModel();
    }

    /**
     * TODO 获取分类列表
     * @param array $data
     * @return array
     */
    public function getAttrList(array $data = [])
    {
        // 当前页码(非必要)
        $current_page = isset($data['current_page']) ? (int)$data['current_page'] : 1;
        // 页面大小(非必要)
        $page_size = isset($data['page_size']) ? (int)$data['page_size'] : (int)ENV('PAGE_SIZE', 50);
        // 获取参数
        $params = [
            'where' => ['dele' => 1, 'attr_group_id' => $data['attr_group_id']] // 删除
        ];

        !empty($data['q']) && $params['like'] = ['attr_value', $data['q']];
        $rst = $this->model->selectInfo($params);
        $total = 0;

        // 数据处理
        if (!empty($rst)) {
            $total = $this->model->total($params);
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
     * TODO 删除分类
     * @param array $data
     * @return bool|int
     */
    public function deleAttr(array $data = [])
    {
        $params = [
            'where' => [
                'attr_id' => $data['attr_id'] // 日志ID
            ],
        ];
        $rst = $this->model->deleInfo($params);
        return $rst;
    }

    /**
     * TODO 分类编辑
     * @param array $data
     * @return array|bool|int
     */
    public function editAttr(array $data = [])
    {
        $params = [
            'where' => ['attr_id' => $data['attr_id']],
            'data' => $data,
        ];
        $rst = $this->model->editInfo($params);
        return $rst;
    }

    /**
     * TODO 添加分类
     * @param array $data
     * @return array|bool|int
     */
    public function addAttr(Array $data = [])
    {
        return $this->model->addInfo($data);
    }
}
