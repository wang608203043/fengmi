<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/14
 * Time: 16:48
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\CouponService;
use think\Request;

class Coupon extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new CouponService();
    }

    /**
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = $this->service->baseList();
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function add(){
        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit(){
        $id = input('id');
        $model = $this->service->getById($id);
        $this->assign('model',$model);
        return $this->fetch();
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(){
        $result = $this->validate(input('post.'),'Coupon');
        if ($result !== true){
            CodeResponse::error(CodeResponse::CODE_PARAMS_ERROR,['error'=>$result]);
        }
        $id = input('id');
        $data['title'] = input('title');
        $data['amount'] = input('amount');
        $data['condition'] = input('condition');
        $res = $this->service->baseSave($id,$data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    public function del(){
        $id = input('id');
        $res = $this->service->delete($id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

}