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

    return 'api: ' . $app->version();
});

$api = app('Dingo\Api\Routing\Router');

// v1 version API
// choose version add this in header    Accept:application/vnd.lumen.v1+json
$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api\V1',
    'prefix' => 'api',
], function ($api) {

    $api->get('/talk', [
        'as' => 'server.talk',
        'uses' => 'ServerController@talk'
    ]);

    $api->post('/login', [
        'as' => 'admin.login',
        'uses' => 'AdminController@login'
    ]);

    $api->any('/server', [
        'as' => 'server.response',
        'uses' => 'ServerController@server'
    ]);

    $api->get('/backupLog2db', [
        'as' => 'server.log',
        'uses' => 'ServerController@backupLog2db'
    ]);

    $api->get('/menu', [
        'as' => 'server.menu',
        'uses' => 'ServerController@menu'
    ]);

    $api->get('/material', [ // 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
        'as' => 'server.material',
        'uses' => 'ServerController@material'
    ]);

    $api->group(['middleware' => 'api'], function ($api) {


        $api->get('/accessToken', [
            'as' => 'server.accessToken',
            'uses' => 'ServerController@accessToken'
        ]);

        $api->get('/autoReply', [
            'as' => 'server.reply',
            'uses' => 'ServerController@autoReply'
        ]);

        //分类管理
        $api->get('/attr/{attr_group_id}', [
            'as' => 'attr.list',
            'uses' => 'AttrController@attrList'
        ]);
        $api->put('/attr', [
            'as' => 'attr.edit',
            'uses' => 'AttrController@attrEdit'
        ]);
        $api->post('/attr', [
            'as' => 'attr.add',
            'uses' => 'AttrController@attrAdd'
        ]);
        $api->delete('/attr', [
            'as' => 'attr.delete',
            'uses' => 'AttrController@attrDele'
        ]);

        // 日志管理
        $api->get('/log', [
            'as' => 'log.list',
            'uses' => 'LogController@logList'
        ]);
        $api->delete('/log', [
            'as' => 'log.dele',
            'uses' => 'LogController@logDele'
        ]);
        $api->put('/log', [
            'as' => 'log.list',
            'uses' => 'LogController@logAttr'
        ]);
        $api->get('/log/{openid}', [
            'as' => 'log.details',
            'uses' => 'LogController@logDetails'
        ]);

        // 媒体管理
        $api->get('/media', [
            'as' => 'media.list',
            'uses' => 'MediaController@mediaList'
        ]);
        $api->get('/media/{media_id}', [
            'as' => 'media.list',
            'uses' => 'MediaController@mediaDetails'
        ]);
        $api->post('/media', [
            'as' => 'media.list',
            'uses' => 'MediaController@mediaAdd'
        ]);
        $api->put('/media', [
            'as' => 'media.edit',
            'uses' => 'MediaController@mediaEdit'
        ]);
        $api->delete('/media', [
            'as' => 'media.dele',
            'uses' => 'MediaController@mediaDele'
        ]);

        // 常用语管理
        $api->get('/general', [
            'as' => 'general.list',
            'uses' => 'GeneralController@generalList'
        ]);
        $api->get('/general/{general_id}', [
            'as' => 'general.list',
            'uses' => 'GeneralController@generalDetails'
        ]);
        $api->post('/general', [
            'as' => 'general.list',
            'uses' => 'GeneralController@generalAdd'
        ]);
        $api->put('/general', [
            'as' => 'general.edit',
            'uses' => 'GeneralController@generalEdit'
        ]);
        $api->delete('/general', [
            'as' => 'general.dele',
            'uses' => 'GeneralController@generalDele'
        ]);

        // 自动回复管理
        $api->get('/auto', [
            'as' => 'auto.list',
            'uses' => 'AutoController@autoList'
        ]);
        $api->get('/auto/{auto_id}', [
            'as' => 'auto.list',
            'uses' => 'AutoController@autoDetails'
        ]);
        $api->post('/auto', [
            'as' => 'auto.list',
            'uses' => 'AutoController@autoAdd'
        ]);

        $api->put('/auto', [
            'as' => 'auto.edit',
            'uses' => 'AutoController@autoEdit'
        ]);

        $api->delete('/auto', [
            'as' => 'auto.dele',
            'uses' => 'AutoController@autoDele'
        ]);


        // 管理员管理
        $api->get('/admin', [
            'as' => 'admin.list',
            'uses' => 'AdminController@adminList'
        ]);
        $api->get('/admin/{admin_id}', [
            'as' => 'admin.details',
            'uses' => 'AdminController@adminDetails'
        ]);

        $api->post('/admin', [
            'as' => 'admin.add',
            'uses' => 'AdminController@adminAdd'
        ]);

        $api->put('/admin', [
            'as' => 'admin.edit',
            'uses' => 'AdminController@adminEdit'
        ]);

        $api->put('/adminState', [
            'as' => 'admin.state',
            'uses' => 'AdminController@adminState'
        ]);

        $api->delete('/admin', [
            'as' => 'admin.dele',
            'uses' => 'AdminController@adminDele'
        ]);
    });
});

