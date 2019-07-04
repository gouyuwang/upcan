<?php

namespace App\Http\Controllers\Pub\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Pub\V1\Logic\QiniuLogic;

class UploadController extends BaseController
{
    //默认允许上传类型 .env
    public function upload(Request $request)
    {
        $data = $this->getData($request);
        $res = (new QiniuLogic)->upload($request, $data);
        return $this->toJson($res);
    }

    public function uploadRadio(Request $request)
    {
        $data = $this->getData($request);
        $res = (new QiniuLogic)->radioUpload($request, $data);
        return $this->toJson($res);
    }

    public function getToken(Request $request)
    {
        $data = $this->getData($request);
        $res = (new QiniuLogic)->getToken($request, $data);
        return $this->toJson($res);
    }
}
