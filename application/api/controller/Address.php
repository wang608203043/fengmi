<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/7
 * Time: 17:58
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\UserAddressService;
use think\Request;

class Address extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new UserAddressService();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(){
        $id = input('id');
        $data['district_id'] = input('district_id');
        $data['auth_id'] = $this->getUid();
        $data['contact_name'] = input('contact_name');
        $data['contact_phone'] = input('contact_phone');
        $data['contact_address'] = input('contact_address');
        $res = $this->service->baseSave($id,$data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * 删除购物车
     * @return \think\response\Json
     */
    public function del(){
        $address_id = input('address_id');
        $res = $this->service->delete($address_id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

}