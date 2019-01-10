<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/7
 * Time: 11:47
 */

namespace app\common\service;


use app\common\component\CodeResponse;
use app\common\model\Cart;
use app\common\model\Coupon;
use app\common\model\District;
use app\common\model\GoodsComment;
use app\common\model\GoodsStock;
use app\common\model\Order;
use think\Cache;
use think\Log;
use think\Queue;
use YYHwxpay\Wxpay;
class OrderService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new Order();
    }

    /**
     * thinkphp分页查询
     * @param array $where
     * @param array $order
     * @param int $listRow
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function baseList($where = [],$order = [],$listRow = 15){
        return $this->model->selectActiveByThinkPage($where,$order,$listRow);
    }

    /**
     * 普通分页
     * @param int $page
     * @param int $listRow
     * @param array $where
     * @param array $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function baseListByPage($page = 1, $listRow = 15, $where = [], $order = []){
        return $this->model->selectActiveByPage($page,$listRow,$where,$order);
    }

    /**
     * @param $id
     * @return $this|array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getById($id){
        return $this->model->findById($id);
    }

    /**
     * @param $id
     * @param $field_values
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function baseSave($id, $field_values){
        return $this->model->saveOrUpdate($id,$field_values);
    }

    /**
     * 软删除
     * @param $id
     * @return int
     */
    public function delete($id){
        return $this->model->deleteById($id);
    }

    /**
     * 强制删除
     * @param $id
     * @return int
     */
    public function deleteForce($id)
    {
        return $this->model->where('id',$id)->delete();
    }

    public function change($id,$field, $value)
    {
        return $this->model->updateField($id,$field,$value);
    }

    /**
     * @param $order_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetail($order_id)
    {
        $order = $this->getById($order_id);
        $stock_list = $order->goodsStock;
        $address = $order->address;
        $address['profile'] = (new District())->getFullAddress($address['district_id']);
        $remark = $order->remark;
        $list = [];
        foreach ($stock_list as $item) {
            $list[] = [
                'gname'=>$item->goods->gname,
                'stock_name'=>$item->stock_name,
                'price'=>$item->price,
                'attribute'=>$item->attribute,
                'img_url'=>$item->img_url,
                'number'=>$item->pivot->number,
            ];
        }
        return [$list,$address,$remark];
    }

    /**
     * @param $goods_stock_id
     * @param $number
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStock($goods_stock_id,$number)
    {
        $data = [];
        $stock = (new GoodsStock())->findById($goods_stock_id);
        $data[] = [
            'goods_stock_id' => $stock->id,
            'gname'=>$stock->goods->gname,
            'attribute'=> $stock->attribute,
            'price'=>$stock->price,
            'img_url'=>$stock->img_url,
            'number'=>$number,
        ];
        return $data;
    }

    /**
     * @param $cart_ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCarts($cart_ids)
    {
        $carts = (new Cart())->whereIn('id',$cart_ids)->select();
        $list = [];
        foreach ($carts as $cart) {
            $list[] = [
                'cart_id'=>$cart->id,
                'goods_stock_id'=>$cart->goodsStock->id,
                'gname'=>$cart->goodsStock->goods->gname,
                'img_url'=>$cart->goodsStock->img_url,
                'price'=>$cart->goodsStock->price,
                'attribute'=>$cart->goodsStock->attribute,
                'number'=>$cart->number,
            ];
        }
        return $list;
    }

    /**
     * @param $data
     * @param $auth_id
     * @param $openid
     * @param $address_id
     * @param $remark
     * @param $coupon_id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pay($data, $auth_id, $openid, $address_id, $remark, $coupon_id)
    {
        $pay['serial'] = uniqueNumber();
        $pay['auth_id'] = $auth_id;
        $pay['remark'] = $remark;
        $coupon = (new Coupon())->findById($coupon_id);
        if ($data['type'] == 'cart'){
            foreach ($data['data'] as $datum) {
                $pay['amount'] += intval($datum['price']*100*$datum['number']);
            }
        }else{
            $pay['amount'] = intval($data['data']['price']*100*$data['data']['number']);
        }
        if ($coupon->condition){
            if ($pay['amount'] < $coupon->condition){
                CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR,null,'优惠券不可用');
            }
            $pay['amount'] = intval($pay['amount'] - $coupon->amount*100);
        }else{
            $pay['amount'] = intval($pay['amount'] - $coupon->amount*100);
        }
        $cache_data = ['pay'=>$pay,'address_id'=>$address_id,'openid'=>$openid,'coupon_id'=>$coupon_id,'goods'=>$data];
        Cache::set($pay['serial'],$cache_data,900);
        return $this->getPayParams($pay, $openid);
    }

    /**
     * @param $data
     * @param $openid
     * @return string
     */
    private function getPayParams($data, $openid)
    {
        $pay_param = array(
            'body' => '岛岛家云南野生蜂蜜',
            'out_trade_no' => $data['serial'],
            'spbill_create_ip' => \request()->ip(),
            'total_fee' => $data['amount'],
            'openid' => $openid,
            'notify_url' => 'https://fengmi.yangshopping.com/api/order/notify' //异步通知地址
        );
        try {
            $pay = new Wxpay(config('wx'));
            $res = $pay->create_order($pay_param);
            if ($res['error'] == 0) {
                $jsParameter = $pay->get_jsbridge_param($res['data']['prepay_id']);
                Log::write($jsParameter, 'PAY_RETURN', true);
                return $jsParameter;
            } else {
                Log::write($res, 'PAY_RETURN_ERROR', true);
                CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR, $res, $res['msg']);
            }
        } catch (\Exception $exception) {
            CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR, $exception->getTrace(), $exception->getMessage());
        }
    }

    /**
     * @param $wx_data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notify($wx_data){
        //out_trade_no total_fee transaction_id
        //生成流水 生成订单 增加用户消费次数,金额  生成订单商品记录  生成订单地址记录 修改库存 修改已售 增加积分
        $inserted = $this->model->where('serial', $wx_data['out_trade_no'])->field('id')->find();
        if (!$inserted){
            $jobHandler = config('job_handler.order_queue');
            $jobQueueName = 'order_queue';
            $jobData = $wx_data;
            $isPushed = Queue::push($jobHandler,$jobData,$jobQueueName);
            if( $isPushed !== false ){
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * @param $auth_id
     * @param $status
     * @param $page
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($auth_id, $status, $page)
    {
        $statusMap = Order::$statusMap;
        if (in_array($status,$statusMap)){
            $data = [];
            $orders = (new Order())->where(['auth_id'=>$auth_id,'status'=>$status])->page($page,3)->select();
            foreach ($orders as $order) {
                $order_goods = $order->goodsStock;
                $goods_list = [];
                foreach ($order_goods as $order_good) {
                    $goods_list[] = [
                        'goods_name' => $order_good->stock_name,
                        'number' => $order_good->pivot['number'],
                        'price' => $order_good->price,
                        'attribute' => $order_good->attribute,
                        'img_url' => $order_good->img_url,
                        'goods_id' => $order_good->goods_id
                    ];
                }
                $data[] = [
                    'order_id' => $order->id,
                    'serial' => $order->serial,
                    'amount' => $order->amount/100,
                    'track_no' => $order->track_no,
                    'status' => $order->status,
                    'status_text' => Order::$statusTextMap[$status],
                    'goods_list' => $goods_list
                ];
            }
            return $data;
        }
        CodeResponse::error(CodeResponse::CODE_PARAMS_ERROR,null,'订单状态出错');
    }

    /**
     * @param $order_id
     * @return int
     */
    public function confirm($order_id)
    {
        return (new Order())->updateField($order_id,['status'=>Order::ORDER_COMMENT,'commenting'=>1],'');
    }

    /**
     * @param $data
     * @return $this|false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function comment($data)
    {
        return (new GoodsComment())->saveOrUpdate(null,$data);
    }

}