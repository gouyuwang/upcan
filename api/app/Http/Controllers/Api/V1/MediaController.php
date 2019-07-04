<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\MediaLogic;
use Illuminate\Http\Request;

class MediaController extends BaseController
{
    private $logic;

    function __construct()
    {
        $this->logic = new MediaLogic();
    }

    /**
     * TODO 获取媒体列表
     * @param Request $request
     * @return mixed
     */
    public function mediaList(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->getMediaList($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 媒体详情
     * @param Request $request
     * @param $mediaId
     * @return mixed
     */
    public function mediaDetails(Request $request, $mediaId)
    {
        $data = $this->getData($request);
        $data['media_id'] = $mediaId;
        $rst = $this->logic->getMediaDetails($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 媒体删除
     * @param Request $request
     * @return mixed
     */
    public function mediaDele(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->deleMedia($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 媒体编辑
     * @param Request $request
     * @return mixed
     */
    public function mediaEdit(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->editMedia($data);
        return $this->jsonData($request, $rst);
    }


    /**
     * TODO 媒体添加
     * @param Request $request
     * @return mixed
     */
    public function mediaAdd(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->addMedia($data);
        return $this->jsonData($request, $rst);
    }
}