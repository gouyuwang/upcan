<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\LogLogic;
use Illuminate\Http\Request;

class LogController extends BaseController
{
    private $logic;

    function __construct()
    {
        $this->logic = new LogLogic();
    }

    /**
     * TODO 获取咨询日志列表
     * @param Request $request
     * @return mixed
     */
    public function logList(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->getLogList($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 日志删除
     * @param Request $request
     * @return mixed
     */
    public function logDele(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->deleLog($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 修改日志分类
     * @param Request $request
     * @return mixed
     */
    public function logAttr(Request $request)
    {
        $data = $this->getData($request);
        $rst = $this->logic->setLogAttr($data);
        return $this->jsonData($request, $rst);
    }

    /**
     * TODO 获取日志详情
     * @param Request $request
     * @return mixed
     */
    public function logDetails(Request $request, $openid)
    {
        $data = $this->getData($request);
        $data['openid'] = $openid;
        $rst = $this->logic->getLogDetails($data);
        return $this->jsonData($request, $rst);
    }
}