<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/12/11
 * Time: 11:13
 */

namespace app\console\job;


use think\Cache;
use think\Db;
use think\Log;
use think\queue\Job;

class OrderQueue
{
    public function fire(Job $job,$data){
        $isDone = $this->execute($data);
        if ($isDone){
            $job->delete();
        }else{
            if ($job->attempts() > 3) {//通过这个方法可以检查这个任务已经重试了几次了
                $job->delete();
            }else{
                $job->release(3); //重新发布任务,延迟三秒执行
            }
        }
    }

    /**
     * @param $data
     * @return bool
     */
    protected function execute($data){
        //执行任务逻辑
        Db::startTrans();
        try {
            $inserted = Db::table('order')->where('serial', $data['out_trade_no'])->field('id')->find();
            if (!$inserted) {
                $cache = Cache::get($data['out_trade_no']);
                $cache['pay']['create_time'] = date('Y-m-d H:i:s');
                $cache['pay']['update_time'] = date('Y-m-d H:i:s');
                $order_id = Db::table('order')->insertGetId($cache['pay']); //生成订单
                if ($order_id) {
                    Db::table('pay')->insert([ //流水
                        'serial' => $data['transaction_id'],
                        'amount' => $data['total_fee'],
                        'order_id' => $order_id,
                        'auth_id' => $cache['pay']['auth_id'],
                        'openid' => $cache['openid'],
                        'trade_time' => time(),
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s'),
                    ]);
                    //生成订单地址
                    $address = Db::table('user_address')->find($cache['address_id']);
                    Db::table('order_address')->insert([
                        'order_id' => $order_id,
                        'district_id' => $address['district_id'],
                        'contact_name' => $address['contact_name'],
                        'contact_phone' => $address['contact_phone'],
                        'contact_address' => $address['contact_address'],
                    ]);
                    //生成订单商品
                    $order_goods = [];
                    if ($cache['goods']['type'] == 'cart') {
                        //删除购物车
                        $carts = [];
                        foreach ($cache['goods']['data'] as $datum) {
                            $stock = Db::table('goods_stock')->where('id', $datum['goods_stock_id'])->find();//修改库存
                            Db::table('goods_stock')->where('id', $datum['goods_stock_id'])
                                ->setDec('stock', $datum['number']);
                            Db::table('goods')->where('id', $stock['goods_id'])
                                ->setInc('saled', $datum['number']);
                            $order_goods[] = [
                                'goods_stock_id' => $datum['goods_stock_id'],
                                'number' => $datum['number'],
                                'order_id' => $order_id
                            ];
                            array_push($carts, $datum['cart_id']);
                        }
                        Db::table('cart')->delete($carts);
                    } else {
                        $order_goods = [
                            'goods_stock_id' => $cache['goods']['data']['goods_stock_id'],
                            'number' => $cache['goods']['data']['number'],
                            'order_id' => $order_id
                        ];
                        $stock = Db::table('goods_stock')
                            ->where('id', $cache['goods']['data']['goods_stock_id'])->find();//修改库存
                        Db::table('goods_stock')->where('id', $cache['goods']['data']['goods_stock_id'])
                            ->setDec('stock', $cache['goods']['data']['number']);
                        Db::table('goods')->where('id', $stock['goods_id'])
                            ->setInc('saled', $cache['goods']['data']['number']);
                    }
                    Db::table('order_goods')->insertAll($order_goods);
                    //修改用户消费次数 增加积分
                    $user = Db::table('wx_auth')->where('id', $cache['pay']['auth_id'])
                        ->field('parent_id')->find();
                    $score = floor($data['total_fee'] / 100);
                    if ($user['parent_id']) {
                        $parent_user_info = Db::table('wx_user')->where('auth_id', $user['parent_id'])->find();
                        $parent_user_info['score'] += $score;
                        $parent_user_info['score_total'] += $score;
                        $parent_user_info['update_time'] += date('Y-m-d H:i:s');
                        Db::table('wx_user')->where('id', $parent_user_info['id'])->update($parent_user_info);
                    }
                    $user_info = Db::table('wx_user')->where('auth_id', $cache['pay']['auth_id'])->find();
                    $user_info['consume_amount'] += $data['total_fee'];
                    $user_info['consume_count'] += 1;
                    $user_info['score'] += $score;
                    $user_info['score_total'] += $score;
                    $user_info['update_time'] = date('Y-m-d H:i:s');
                    //Db::table('wx_user')->where('id', $user_info['id'])->update($user_info);
                    //修改优惠券状态
                    if ($cache['coupon_id']){
                        Db::table('receive')->where('id', $cache['coupon_id'])->setField('used', 1);
                    }
                }
            }
            Db::commit();
            return true;
        } catch (\Exception $exception) {
            Db::rollback();
            return false;
        }
    }

    public function failed($jobData){
        //处理失败任务数据
        $cache_data = Cache::get($jobData['out_trade_no']);
        Db::table('order_failed')->insert([
            'wx_data'=>json_encode($jobData),
            'order_data'=>json_encode($cache_data),
            'create_time'=> date('Y-m-d H:i:s'),
            'update_time'=> date('Y-m-d H:i:s')
        ]);
    }
}