<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/22
 * Time: 19:20
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\AuthService;
use app\common\service\UserService;
use think\Request;

class User extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new AuthService();
    }

    /**
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = $this->service->baseList();
        $this->assign('list',$list);
        return $this->fetch();
    }

}