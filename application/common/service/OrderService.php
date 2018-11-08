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
use app\common\model\District;
use app\common\model\GoodsStock;
use app\common\model\Order;
use app\common\model\OrderAddress;
use app\common\model\Pay;
use app\common\model\User;
use app\common\model\UserAddress;
use think\Cache;
use think\Log;
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

    public function pay($data,$auth_id,$openid,$address_id,$remark)
    {
        $pay['serial'] = build_order_no();
        $pay['auth_id'] = $auth_id;
        $pay['remark'] = $remark;
        if ($data['type'] == 'cart'){
            foreach ($data['data'] as $datum) {
                $pay['amount'] += intval($datum['price']*100*$datum['number']);
            }
        }else{
            $pay['amount'] = intval($data['data']['price']*100*$data['data']['number']);
        }
        Cache::set($pay['serial'],$pay,900);
        Cache::set($pay['serial'].'_address',$address_id,900);
        Cache::set($pay['serial'].'_openid',$openid,900);
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
            'body' => '青木e卡',
            'out_trade_no' => $data['serial'],
            'spbill_create_ip' => \request()->ip(),
            'total_fee' => $data['amount'],
            'openid' => $openid,
            'notify_url' => 'https://api.yangshopping.com/fengmi/public/index.php/api/order/notify' //异步通知地址
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
     * @throws \think\exception\PDOException
     */
    public function notify($wx_data){
        //out_trade_no total_fee transaction_id
        //生成流水 生成订单 增加用户消费次数,金额  生成订单商品记录  生成订单地址记录 修改库存
        $cache = Cache::get($wx_data['out_trade_no']);
        if($cache){
            try {
                $this->model->startTrans();
                $inserted = $this->model->where('serial',$wx_data['out_trade_no'])->find();
                if (!$inserted){
                    $order = (new Order())->saveOrUpdate(null,$cache); //生成订单
                    if ($order){
                        $address_id = Cache::get($wx_data['out_trade_no'].'_address');
                        $order->pay()->save([ //流水
                            'serial'=> $wx_data['transaction_id'],
                            'amount'=>$wx_data['total_fee'],
                            'order_id'=>$order->id,
                            'auth_id'=>$cache['auth_id'],
                            'openid'=>Cache::get($wx_data['out_trade_no'].'_openid'),
                            'trade_time'=>time(),
                        ]);
                        //生成订单地址
                        $address = (new UserAddress())->findById($address_id);
                        $order->address()->save([
                            'district_id'=>$address->district_id,
                            'contact_name'=>$address->contact_name,
                            'contact_phone'=>$address->contact_phone,
                            'contact_address'=>$address->contact_address,
                        ]);
                        //生成订单商品
                        $order_goods = [];
                        $goodsStock = new GoodsStock();
                        if ($cache['type'] == 'cart'){
                            foreach ($cache['data'] as $datum) {
                                $goodsStock->where('id',$datum['goods_stock_id'])->setField('number',$datum['number']);//修改库存
                                $order_goods[] = [
                                    'goods_stock_id'=>$datum['goods_stock_id'],
                                    'number'=>$datum['number']
                                ];
                            }
                        }else{
                            $order_goods = [
                                'goods_stock_id'=>$cache['data']['goods_stock_id'],
                                'number'=>$cache['data']['number']
                            ];
                            $goodsStock->where('id',$cache['data']['goods_stock_id'])->setField('number',$cache['data']['number']);
                        }
                        $order->goodsStock()->saveAll($order_goods);
                        //修改用户消费次数
                        $user_info = $order->user->user;
                        $user_info->consume_amount += $wx_data['total_fee'];
                        $user_info->consume_count += 1;
                        $user_info->save();
                        $this->model->commit();
                    }
                }
            } catch (\Exception $exception) {
                $this->model->rollback();
            }
        }
    }

}