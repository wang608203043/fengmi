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
use app\common\model\Auth;
use app\common\model\Cart;
use app\common\model\Coupon;
use app\common\model\District;
use app\common\model\GoodsComment;
use app\common\model\GoodsStock;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\Receive;
use app\common\model\UserAddress;
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
        $stock = (new GoodsStock())->findById($goods_stock_id);
        $data = [
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
        $total = 0;
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
            $total += $cart->goodsStock->price * $cart->number;
        }
        return ['list' => $list,'total' => $total];
    }

    /**
     * @param $data
     * @param $auth_id
     * @param $openid
     * @param $address_id
     * @param $remark
     * @param $coupon_id
     * @return array
     * @throws \think\exception\PDOException
     */
    public function pay($data, $auth_id, $openid, $address_id, $remark, $coupon_id)
    {
        //生产订单   删除购物车 生成订单商品记录  生产订单地址  更新用户优惠券状态
        $pay['serial'] = uniqueNumber();
        $pay['auth_id'] = $auth_id;
        $pay['remark'] = $remark;
        $pay['amount'] = 0;
        $this->model->startTrans();
        try {
            $userCoupon = (new Receive())->where(['auth_id'=>$auth_id,'coupon_id'=>$coupon_id,'used'=>0])->find();
            $address = (new UserAddress())->findById($address_id);
            //创建订单
            $order = $this->model->saveOrUpdate(null,$pay);
            //创建订单地址
            $order_address_data = [
                'district_id' => $address->district_id,
                'contact_name' => $address->contact_name,
                'contact_phone' => $address->contact_phone,
                'contact_address' => $address->contact_address,
            ];
            $order->address()->save($order_address_data);
            //创建订单商品记录
            $order_goods_data = [];
            if ($data['type'] == 'cart'){
                $cart_ids = [];
                $cartModel = new Cart();
                foreach ($data['data'] as $datum) {
                    //修改库存
                    $this->model->goodsStock()->where('id', $datum['goods_stock_id'])
                        ->setDec('stock', $datum['number']);
                    $order_goods_data[] = [
                        'order_id' => $order->id,
                        'goods_stock_id' => $datum['goods_stock_id'],
                        'number' => $datum['number']
                    ];
                    //计算金额
                    $pay['amount'] += intval($datum['price']*100*$datum['number']);
                    array_push($cart_ids, $datum['cart_id']);
                }
                //删除购物车
                $cartModel->whereIn('id',$cart_ids)->delete();
            }else{
                $order_goods_data[] = [
                    'order_id' => $order->id,
                    'goods_stock_id' => $data['data']['goods_stock_id'],
                    'number' => $data['data']['number']
                ];
                //修改库存
                $this->model->goodsStock()->where('id', $data['data']['goods_stock_id'])
                    ->setDec('stock', $data['data']['number']);
                $pay['amount'] = intval($data['data']['price']*100*$data['data']['number']);
            }
            (new OrderGoods())->saveAll($order_goods_data);
            //使用优惠券
            if ($userCoupon){
                if ($userCoupon->coupon->condition){
                    if ($pay['amount'] < $userCoupon->coupon->condition){
                        CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR,null,'优惠券不可用');
                    }
                    $pay['amount'] = intval($pay['amount'] - $userCoupon->coupon->amount*100);
                }else{
                    $pay['amount'] = intval($pay['amount'] - $userCoupon->coupon->amount*100);
                }
                $pay['auth_coupon_id'] = $userCoupon->id;
                //更新优惠券状态
                $userCoupon->used = 1;
                $userCoupon->save();
            }
            //更新订单金额
            $order->amount = $pay['amount']/100;
            $order->save();
            $this->model->commit();
            //获取支付参数
            $jsParameter = $this->getPayParams($pay, $openid);
            return ['jsParameter' => $jsParameter,'order_id' => $order->id];
        } catch (\Exception $exception) {
            $this->model->rollback();
            CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR, $exception->getTrace(), $exception->getMessage());
        }
        return null;
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
            'notify_url' => 'https://mini.daodaoshop.com/api/order/notify' //异步通知地址
        );
        $pay = new Wxpay(config('wx'));
        $res = $pay->create_order($pay_param);
        if ($res['error'] == 0) {
            $jsParameter = $pay->get_jsbridge_param($res['data']['prepay_id']);
            return $jsParameter;
        } else {
            Log::write($res, 'PAY_RETURN_ERROR', true);
            CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR, $res, $res['msg']);
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
        //生成流水 增加用户消费次数,金额  修改已售 增加积分
        $order = $this->model->findOne(['serial' => $wx_data['out_trade_no']]);
        $this->model->startTrans();
        try {
            //支付流水
            $order->pay()->save([
                'serial' => $wx_data['transaction_id'],
                'amount' => $wx_data['total_fee'],
                'openid' => $wx_data['openid'],
                'trade_time' => time(),
            ]);
            //增加用户消费次数,金额 增加积分
            $score = floor($wx_data['total_fee'] / 100);
            if ($order->user->parent_id) {
                //用户上级增加积分
                $parent = (new Auth())->findOne(['parent_id'=>$order->user->parent_id]);
                $parent_user_info = $parent->user;
                $parent_user_info->score += $score;
                $parent_user_info->score_total += $score;
                $parent_user_info->save();
            }
            $user_info = $order->user->user;
            $user_info->consume_amount += $wx_data['total_fee'];
            $user_info->consume_count += 1;
            $user_info->score += $score;
            $user_info->score_total += $score;
            $user_info->save();
            //修改已售 todo
            //修改订单状态
            $order->status = Order::ORDER_PENDING_SEND;
            $order->save();
            $this->model->commit();
        } catch (\Exception $exception) {
            $this->model->rollback();
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
            $orders = (new Order())->with('goodsStock')->where(['auth_id'=>$auth_id,'status'=>$status])
                ->page($page,10)->select();
            foreach ($orders as $order) {
                $order_goods = $order->goodsStock;
                $goods_list = [];
                foreach ($order_goods as $order_good) {
                    $goods_list[] = [
                        'goods_name' => $order_good->stock_name,
                        'number' => $order_good->pivot['number'],
                        'price' => $order_good->price,
                        'attribute' => $order_good->attribute,
                        'img_url' => $order_good->goods->img_url,
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
        $order = $this->model->findById($data['order_id']);
        $this->model->startTrans();
        try {
            $order->status = Order::ORDER_DONE;
            $order->save();
            (new GoodsComment())->saveOrUpdate(null,[
                'goods_id' => $data['goods_id'],
                'auth_id' => $data['auth_id'],
                'content' => $data['content'],
            ]);
            $this->model->commit();
        } catch (\Exception $exception) {
            $this->model->rollback();
            CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR);
        }
        return true;
    }

    /**
     * @param $order_id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function paying($order_id)
    {
        $order = (new Order())->findById($order_id);
        $order->amount = $order->amount*100;
        $openid = $order->user->openid;
        $jsParameter = $this->getPayParams($order,$openid);
        return $jsParameter;
    }

    /**
     * @param $order_id
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancel($order_id)
    {
        $order = (new Order())->findById($order_id);
        //回复库存
        $goodsStocks = $order->goodsStock;
        foreach ($goodsStocks as $goodsStock) {
            $goodsStock->stock += $goodsStock->pivot['number'];
            $goodsStock->save();
        }
        $order->status = Order::ORDER_CANCEL;
        return $order->save();
    }

}