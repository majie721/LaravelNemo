<?php

namespace LaravelNemo\Facades;

use App\Models\Menu;
use Illuminate\Support\Facades\Facade;


/**
 * @method static \LaravelNemo\Library\ApiResponse::success()
 * @method static \LaravelNemo\Library\ApiResponse::error()
 * @method static \LaravelNemo\Library\ApiResponse::data()
 * @method static \LaravelNemo\Library\ApiResponse::download()
 * @method static \LaravelNemo\Library\ApiResponse::downloadByFile()
 * @method static \LaravelNemo\Library\ApiResponse::preview()
 *  @mixin \LaravelNemo\Library\ApiResponse
 */
class ApiResponse extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ApiResponse';
    }
}
