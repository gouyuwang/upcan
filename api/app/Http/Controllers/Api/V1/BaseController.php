<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Logic\UserLogic;
use App\Http\Controllers\Controller;
use App\Extend\JWT;
use App\Models\UserFormIdModel;

class BaseController extends Controller
{
    public function __construct()
    {

    }

    //数据解析函数
    public function getData($request)
    {
        $key = ENV('JWT_SECRET');//jwt_key
        $all = $request->all();
        // //判断get请求和其他请求
        $obj = isset($all['token']) ? JWT::decode($all['token'], $key, array('HS256')) : 'token不得为空';
//        return $obj;
        if (is_object($obj)) {
            $all['_role_id'] = $obj->{'_role_id'};
            $all['_admin_id'] = $obj->{'_admin_id'};
            unset($all['token']);
        }
        if (isset($all['debug'])) {
            print_r($all);
            exit;
        }
        return $all;
    }

    //保存formId
    public function saveFormId($arr)
    {

        if (!empty($arr['formIds'])) {
            if (!is_array($arr['formIds'])) {
                $form = json_decode($arr['formIds'], true);
            } else
                $form = $arr['formIds'];

            for ($i = 0; $i < count($form); ++$i) {
                $str = substr($form[$i], 0, 1);
                if ($str == 1) {
                    $map = [
                        'formId' => $form[$i],
                        'user_id' => $arr['user_id'],
                    ];
                    $res = (new UserFormIdModel)->addInfo($map);
                    if (!$res) continue;
                }
            }
        }
    }

}
