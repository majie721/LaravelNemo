<?php

namespace LaravelNemo\Front\Controllers;

class BaseController
{

    protected function response($data=[],$code=0,$message='success'){
        return [
            'code'=>$code,
            'message'=>$message,
            'data'=>$data,
        ];
    }


}
