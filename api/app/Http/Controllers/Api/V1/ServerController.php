<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\ServerLogic;
use Illuminate\Http\Request;

class ServerController extends BaseController
{

    private $logic;

    function __construct()
    {
        $this->logic = new ServerLogic();
    }

    /**
     * Todo 微信服务器响应
     * @param Request $request
     * @return mixed
     */
    public function server(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->server($data);
        return $this->toJson($rst);
    }

    /**
     * TODO 菜单管理
     * @param Request $request
     * @return mixed
     */
    public function menu(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->setMenu($data);
        return $this->toJson($rst);
    }

    /**
     * TODO  素材列表
     * @param Request $request
     * @return mixed
     */
    public function material(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->getMaterial($data);
        return $this->toJson($rst);
    }


    /**
     * TODO 自动回复
     * @param Request $request
     * @return mixed
     */
    public function autoReply(Request $request)
    {
        return $this->toJson($this->logic->autoReply($this->getData($request)));
    }

    /**
     * TODO 数据备份到数据库中
     * @param Request $request
     * @return mixed
     */
    public function backupLog2db(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->backupLog2db($data);
        return $this->toJson($rst);
    }

    /**
     * TODO 获取客服聊天记录
     * @param Request $request
     * @return mixed
     */
    public function talk(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->getStaffLog($data);
        $arr = [];
        foreach ($rst as $v) {
            $arr[] = is_array($v) ? $v : (array)$v;
        }
        return $this->jsonData($request, $arr);
    }

}