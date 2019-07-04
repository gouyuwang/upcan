<?php
/**
 *  媒体逻辑
 */

namespace App\Http\Controllers\Api\V1\Logic;

use App\Models\MediaModel;

class MediaLogic extends BaseLogic
{
    private $model;

    function __construct()
    {
        $this->model = new MediaModel();
    }

    /**
     * TODO 获取媒体列表
     * @param array $data
     * @return array
     */
    public function getMediaList(array $data = [])
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
                ['media_id', 'DESC']
            ],
        ];
        !empty($data['q']) && $params['like'] = ['media_desc', $data['q']];
        $rst = $this->model->selectInfo($params,false);
        $total = 0;

        // 数据处理
        if (!empty($rst)) {
            $total = $this->model->total($params);
            foreach ($rst as &$v) {
                $v['url'] = empty($v['url']) ? '' : $this->formatUrl($v['url'], 1);
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
     * TODO 获取媒体详情
     * @param array $data
     * @return array|string
     */
    public function getMediaDetails(array $data = [])
    {
        $params = [
            'where' => [
                'media_id' => $data['media_id'],
                'dele' => 1
            ],
        ];
        $rst = $this->model->findInfo($params);
        return $rst;
    }

    /**
     * TODO 媒体编辑
     * @param array $data
     */
    public function editMedia(array $data = [])
    {
        $params = [
            'where' => ['media_id' => $data['media_id']],
            'data' => $data,
        ];
        $rst = $this->model->editInfo($params);
        return $rst;
    }

    /**
     * TODO 媒体删除
     * @param array $data
     * @return mixed
     */
    public function deleMedia(array $data = [])
    {
        $params = [
            'where' => ['media_id' => $data['media_id']],
        ];
        $rst = $this->model->deleInfo($params);
        return $rst;
    }

    /**
     * TODO 添加媒体
     * @param array $data
     * @return array|bool|int
     */
    public function addMedia(array $data = [])
    {
        return $this->model->addInfo($data);
    }
}
