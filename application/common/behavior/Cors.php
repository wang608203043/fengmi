<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/16
 * Time: 13:28
 */

namespace app\common\behavior;


class Cors
{
    public function run(){
        \app\common\component\Cors::check();
    }
}