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


Route::prefix('nemo')->middleware([])->group(function (){
    Route::get('/',static function(){
       return redirect('/nemo/tools/index');
    });

    $config =  config('nemo.route.nemo',[]);
    Route::any('{controller}/{action}', static function ($controller, $action)use ($config){
        return \LaravelNemo\Library\Router::dispatchRoute($controller,$action,$config);
    })->where('controller','.*');
});
