<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/16
 * Time: 12:51
 */

namespace app\common\component;


use think\Log;
use think\Response;

class Cors
{
    /**
     * @var array Basic headers handled for the CORS requests.
     */
    public static $cors_header = [
        'Access-Control-Allow-Origin' => ['*'],
        'Access-Control-Allow-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        'Access-Control-Allow-Headers' => ['*'],
        //'Access-Control-Allow-Credentials' => true, // cookie携带
        'Access-Control-Max-Age' => 86400,
        'Access-Control-Expose-Headers' => [],
    ];

    /**
     * 跨域检查
     */
    public static function check(){
        foreach (self::$cors_header as $key=>$item) {
            header($key.':'.(is_array($item) ? implode(',',$item) : $item));
        }
        if (strtoupper(request()->method()) == 'OPTIONS'){
            exit(0);
        }

    }
}