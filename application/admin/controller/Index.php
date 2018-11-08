<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2018/6/9
 * Time: 9:50
 */

namespace app\admin\controller;


class Index extends BaseController
{
    public function index(){
        return $this->fetch();
    }

    public function welcome(){
        return $this->fetch();
    }

}