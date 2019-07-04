<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\AdminLogic;
use App\Http\Controllers\Api\V1\Logic\ServerLogic;
use Illuminate\Http\Request;

class AdminController extends BaseController
{

    private $logic;

    function __construct()
    {
        $this->logic = new AdminLogic();
    }

    /**
     * Todo 管理员登录
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->checkLogin($data);
        return $this->toJson($rst);
    }

    /**
     * TODO 管理员列表
     * @param Request $request
     * @return mixed
     */
    public function adminList(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->getAdminList($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 获取管理员详情
     * @param Request $request
     * @return mixed
     */
    public function adminDetails(Request $request, $adminId)
    {
        $data = $this->getData($request);
        $data['admin_id'] = $adminId;
        $rst = $this->logic->getAdminDetails($data);
        return $this->jsonData($request, $rst);
    }


    /**
     * TODO 编辑管理员信息
     * @param Request $request
     * @return mixed
     */
    public function adminEdit(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->editAdmin($data);
        return $this->toJson($rst);
    }

    /**
     * TODO 设置管理员状态
     * @param Request $request
     * @return mixed
     */
    public function adminState(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->setAdminState($data);
        return $this->toJson($rst);
    }

    /**
     * TODO 添加管理员
     * @param Request $request
     * @return mixed
     */
    public function adminAdd(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->addAdmin($data);
        return $this->toJson($rst);
    }

    /**
     * TODO 删除管理员信息
     * @param Request $request
     * @return mixed
     */
    public function adminDele(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->deleteAdmin($data);
        return $this->toJson($rst);
    }


}