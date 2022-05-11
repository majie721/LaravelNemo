<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware([])->group(function (){
    $config =  config('nemo.route.nemo',[]);
    Route::any('{controller}/{action}', static function ($controller, $action)use ($config){
        return \LaravelNemo\Library\Router::dispatchRoute($controller,$action,$config);
    })->where('controller','.*');
});
