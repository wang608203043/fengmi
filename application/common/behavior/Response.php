<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/15
 * Time: 16:50
 */

namespace app\common\behavior;


use think\Log;
use think\Request;

class Response
{
    /**
     * @param \think\Response $response
     */
    public function run($response){
        /*$data = $response->getData(); //响应数据
        if (Request::instance()->isAjax()){
            $result = [];

            Log::write($data,'log',true);
        }
        Log::write($response,'log',true);*/
    }
}