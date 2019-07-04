<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\AttrLogic;
use Illuminate\Http\Request;

class AttrController extends BaseController
{
    private $logic;

    function __construct()
    {
        $this->logic = new AttrLogic();
    }

    /**
     * TODO 获取分类列表
     * @param Request $request
     * @return mixed
     */
    public function attrList(Request $request, $attrGroupId)
    {
        $data = $this->getData($request);
        $data['attr_group_id'] = $attrGroupId;
        $rst = $this->logic->getAttrList($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 分类删除
     * @param Request $request
     * @return mixed
     */
    public function attrDele(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->deleAttr($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 分类编辑
     * @param Request $request
     * @return mixed
     */
    public function attrEdit(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->editAttr($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 分类添加
     * @param Request $request
     * @return mixed
     */
    public function attrAdd(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->addAttr($data);
        return $this->jsonData($request, $rst);
    }
}