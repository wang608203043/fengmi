<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/24
 * Time: 14:31
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\OrderService;
use think\Request;

class Order extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new OrderService();
    }

    /**
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $keywords = input('keywords');
        $status = input('status', 0);
        $where = [];
        if ($keywords){
            $where['serial'] = ['like','%'.$keywords.'%'];
        }
        if ($status !== null){
            $where['status'] = $status;
        }
        $list = (new OrderService())->baseList($where,['create_time'=>'desc']);
        $this->assign('list', $list);
        $this->assign('status', $status);
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function send()
    {
        $id = input('order_id');
        $data['track_no'] = input('track_no');
        $data['status'] = 1;
        $res = $this->service->baseSave($id, $data);
        return $res ? CodeResponse::format($res) : CodeResponse::fail();
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(){
        $order_id = input('order_id');
        list($goodsList,$address,$remark) = $this->service->getDetail($order_id);
        $this->assign('goodsList',$goodsList);
        $this->assign('address',$address);
        $this->assign('remark',$remark);
        return $this->fetch();
    }

    public function del(){
        $id = input('id');
        $res = $this->service->delete($id);
        return $res ? CodeResponse::format($res) : CodeResponse::fail();
    }
}