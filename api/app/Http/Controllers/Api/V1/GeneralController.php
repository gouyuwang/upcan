<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\GeneralLogic;
use Illuminate\Http\Request;

class GeneralController extends BaseController
{
    private $logic;

    function __construct()
    {
        $this->logic = new GeneralLogic();
    }

    /**
     * TODO 获取常用语列表
     * @param Request $request
     * @return mixed
     */
    public function generalList(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->getGeneralList($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 常用语详情
     * @param Request $request
     * @param $mediaId
     * @return mixed
     */
    public function generalDetails(Request $request, $generalId)
    {
        $data = $this->getData($request);
        $data['general_id'] = $generalId;
        $rst = $this->logic->getGeneralDetails($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 常用语删除
     * @param Request $request
     * @return mixed
     */
    public function generalDele(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->deleGeneral($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 常用语编辑
     * @param Request $request
     * @return mixed
     */
    public function generalEdit(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->editGeneral($data);
        return $this->jsonData($request, $rst);
    }


    /**
     * TODO 媒体添加
     * @param Request $request
     * @return mixed
     */
    public function generalAdd(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->addGeneral($data);
        return $this->jsonData($request, $rst);
    }
}