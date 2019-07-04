<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$app->get('/', function () use ($app) {
    return '公共接口' . $app->version();
});

$api = app('Dingo\Api\Routing\Router');

// v1 version API
// choose version add this in header    Accept:application/vnd.lumen.v1+json
$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Pub\V1',
    'prefix' => 'public',
], function ($api) {

    // 二维码
    $api->get('/QR', [
        'as' => 'pub.qr',
        'uses' => 'UploadController@QR'
    ]);

    // 图片上传
    $api->post('/uploads', [
        'as' => 'Upload.upload',
        'uses' => 'UploadController@upload'
    ]);

    // 获取短信验证码
    $api->post('/sendSmsCaptcha', [
        'as' => 'sms.captcha',
        'uses' => 'SmsController@sendSmsCaptcha'
    ]);

    // 获取分类属性
    $api->get('/attr/{attr_group_id}', [
        'as' => 'attr.list',
        'uses' => 'AttrController@attr'
    ]);

    // 快速入驻
    $api->post('/fastSignup', [
        'as' => 'inst.signup',
        'uses' => 'InstController@fastSignup'
    ]);

    // 获取上传token
    $api->get('/getToken', [
        'as' => 'Upload.token',
        'uses' => 'UploadController@getToken'
    ]);
});
