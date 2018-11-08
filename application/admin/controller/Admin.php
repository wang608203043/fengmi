<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/17
 * Time: 17:23
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\AdminUserService;
use think\Request;

class Admin extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new AdminUserService();
    }

    /**
     * 登陆页面
     * @return mixed
     */
    public function index(){
        return $this->fetch('login/index');
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(){
        $account = input('post.account');
        $password = input('post.password');
        $res = $this->service->getAdminByLogin($account,$password);
        return $res ? CodeResponse::format() : CodeResponse::fail(CodeResponse::CODE_ERROR_ACCOUNT_PASSWORD);
    }

    /**
     * @return \think\response\Json
     */
    public function modify(){
        $password = input('post.new_password');
        $origin_password = input('post.origin_password');
        $id = session('admin_user')['id'];
        if (md5($origin_password) == session('admin_user')['password']){
            $res = $this->service->modifyPassword($id,$password);
            return $res ? CodeResponse::format() : CodeResponse::fail();
        }
        return CodeResponse::fail(CodeResponse::CODE_PARAMS_ERROR,null,'原密码错误');
    }

    public function logout(){
        session('admin_user',null);
        $this->redirect('index');
    }
}