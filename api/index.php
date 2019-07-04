<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/')
);

// phpunit 报错？？？
$app->withFacades();

// jwt
class_alias('Tymon\JWTAuth\Facades\JWTAuth', 'JWTAuth');
class_alias('Tymon\JWTAuth\Facades\JWTFactory', 'JWTFactory');

//QRCODE
class_alias('SimpleSoftwareIO\QrCode\Facades\QrCode', 'QrCode');

// Mongo Database
class_alias('Jenssegers\Mongodb\MongodbServiceProvider', 'Mongo');

//mail
//class_alias('Illuminate\Support\Facades\Mail', 'mail');

$app->withEloquent();

//config
// jwt
$app->configure('jwt');
// mail
$app->configure('mail');
// cors 配置
$app->configure('cors');

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    'cors' => App\Http\Middleware\Cors::class,
]);

$app->routeMiddleware([
    'api' => App\Http\Middleware\api::class,
    'end' => App\Http\Middleware\AfterMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);

$app->register(App\Providers\AuthServiceProvider::class);

//redis
$app->register(Illuminate\Redis\RedisServiceProvider::class);


// 注入repository
$app->register(App\Providers\RepositoryServiceProvider::class);

// dingo/api
$app->register(Dingo\Api\Provider\LumenServiceProvider::class);

//jwt
$app->register(Tymon\JWTAuth\Providers\LumenServiceProvider::class);


//QRCODE
$app->register(SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class);



$app->withEloquent();

app('Dingo\Api\Auth\Auth')->extend('jwt', function ($app) {
    return new Dingo\Api\Auth\Provider\JWT($app['Tymon\JWTAuth\JWTAuth']);
});

//Injecting auth
$app->singleton(Illuminate\Auth\AuthManager::class, function ($app) {
    return $app->make('auth');
});

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/


$app->group(['namespace' => 'App\Http\Controllers', 'domain' => 'api.upcanrobot.com'], function ($app) {
    require __DIR__ . '/app/Http/Routes/routes.php';
});

// 小程序端数据接口
$app->group(['prefix' => 'api'], function ($app) {
    require __DIR__ . '/app/Http/Routes/api.php';
});
 
 //公共接口
$app->group(['prefix' => 'public'], function ($app) {
    require __DIR__ . '/app/Http/Routes/public.php';
});

$app->run();
