<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/12/11
 * Time: 15:22
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\IntegralOrderService;
use think\Request;

class IntegralOrder extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new IntegralOrderService();
    }

    /**
     * @return \think\response\Json
     * @throws \think\exception\PDOException
     */
    public function create(){
        $integral_id = input('integral_id');
        $address_id = input('address_id');
        $auth_id = $this->getUid();
        $res = $this->service->createOrder($auth_id,$integral_id,$address_id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }
}