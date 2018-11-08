<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/16
 * Time: 15:26
 */

namespace app\common\behavior;


use app\common\component\Token;

class TokenAuth
{
    public function run($request){
        (new Token())->check_token($request);
    }
}