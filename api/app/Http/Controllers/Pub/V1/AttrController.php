<?php

namespace App\Http\Controllers\Pub\V1;

use App\Http\Controllers\Pub\V1\Logic\RedisLogic;
use Illuminate\Http\Request;

class AttrController extends BaseController
{

    /**
     * TODO 获取分类
     * @param Request $request
     * @param $attrGroupId
     * @return mixed
     */
    public function attr(Request $request, $attrGroupId)
    {
        $rst = (new RedisLogic())->attr($attrGroupId);
        return $this->jsonData($request, $rst);
    }

}
