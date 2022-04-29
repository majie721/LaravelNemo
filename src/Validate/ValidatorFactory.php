<?php
namespace LaravelNemo\Validate;

use Illuminate\Support\Facades\Validator;

class ValidatorFactory
{
    public static function getValidator(array $data, array $rules, array $messages = [], array $customAttributes = []){
        //todo待优化
        return Validator::make( $data,  $rules,  $messages = [],  $customAttributes = []);
    }
}
