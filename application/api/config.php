<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    'token'=>[
        'on'=> false,
        'token_key'=>'x-token-key',
        'expire'=> 7200,
        'iss' => 'sys_'
    ],
    'white_space'=>[
        'api/User/getSessionId',
        'api/User/updateUser',
        'api/Category/getBanner',
        'api/Merchant/getAccessToken',
        'api/Utils/upload',
    ],
];
