<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/7
 * Time: 17:23
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\CartService;
use think\Request;

class Cart extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new CartService();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(){
        $id = input('id');
        $data['goods_stock_id'] = input('goods_stock_id');
        $data['auth_id'] = $this->getUid();
        $data['number'] = input('number');
        $res = $this->service->baseSave($id,$data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * 删除购物车
     * @return \think\response\Json
     */
    public function del(){
        $cart_id = input('cart_id');
        $res = $this->service->deleteForce($cart_id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * 修改数量
     * @return \think\response\Json
     */
    public function changeNumber(){
        $cart_id = input('cart_id');
        $number = input('number');
        $res = $this->service->change($cart_id,'number',$number);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        $page = input('page',1);
        $auth_id = $this->getUid();
        $list = $this->service->getCarts($auth_id,$page);
        return CodeResponse::format($list);
    }
}