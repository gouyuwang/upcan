<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\AutoLogic;
use Illuminate\Http\Request;

class AutoController extends BaseController
{
    private $logic;

    function __construct()
    {
        $this->logic = new AutoLogic();
    }

    /**
     * TODO 获取列表
     * @param Request $request
     * @return mixed
     */
    public function autoList(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->getAutoList($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 回复详情
     * @param Request $request
     * @param $mediaId
     * @return mixed
     */
    public function autoDetails(Request $request, $autoId)
    {
        $data = $this->getData($request);
        $data['auto_id'] = $autoId;
        $rst = $this->logic->getAutoDetails($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 删除
     * @param Request $request
     * @return mixed
     */
    public function autoDele(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->deleAuto($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 编辑
     * @param Request $request
     * @return mixed
     */
    public function autoEdit(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->editAuto($data);
        return $this->jsonData($request, $rst);
    }


    /**
     * TODO 添加
     * @param Request $request
     * @return mixed
     */
    public function autoAdd(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->addAuto($data);
        return $this->jsonData($request, $rst);
    }
}