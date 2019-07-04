<?php
/**
 *  常用语逻辑
 */

namespace App\Http\Controllers\Api\V1\Logic;

use App\Models\GeneralModel;

class GeneralLogic extends BaseLogic
{
    private $model;

    function __construct()
    {
        $this->model = new GeneralModel();
    }

    /**
     * TODO 获取常用语列表
     * @param array $data
     * @return array
     */
    public function getGeneralList(array $data = [])
    {
        // 当前页码(非必要)
        $current_page = isset($data['current_page']) ? (int)$data['current_page'] : 1;
        // 页面大小(非必要)
        $page_size = isset($data['page_size']) ? (int)$data['page_size'] : (int)ENV('PAGE_SIZE', 50);
        $params = [
            'where' => [
                'dele' => 1,  // 未删除
            ],
            'limit' => [($current_page - 1) * $page_size, $page_size],
            'order' => [
                ['general_id', 'DESC']
            ],
        ];
        !empty($data['q']) && $params['like'] = ['general_text', $data['q']];
        $rst = $this->model->selectInfo($params,false);
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
     * TODO 获取详情
     * @param array $data
     * @return array|string
     */
    public function getGeneralDetails(array $data = [])
    {
        $params = [
            'where' => [
                'general_id' => $data['general_id'],
                'dele' => 1
            ],
        ];
        $rst = $this->model->findInfo($params);
        return $rst;
    }

    /**
     * TODO 常用语编辑
     * @param array $data
     */
    public function editGeneral(array $data = [])
    {
        $params = [
            'where' => ['general_id' => $data['general_id']],
            'data' => $data,
        ];
        $rst = $this->model->editInfo($params);
        return $rst;
    }

    /**
     * TODO 删除
     * @param array $data
     * @return mixed
     */
    public function deleGeneral(array $data = [])
    {
        $params = [
            'where' => ['general_id' => $data['general_id']],
        ];
        $rst = $this->model->deleInfo($params);
        return $rst;
    }

    /**
     * TODO 添加
     * @param array $data
     * @return array|bool|int
     */
    public function addGeneral(array $data = [])
    {
        return $this->model->addInfo($data);
    }
}
