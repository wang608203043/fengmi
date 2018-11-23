<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/22
 * Time: 15:12
 */

namespace app\console\command;


use Redis;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class OrderQueue extends Command
{
    protected function configure()
    {
        $this->setName('order:queue')->setDescription('consume order queue');
    }

    protected function execute(Input $input, Output $output)
    {
        ini_set('default_socket_timeout', -1);
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->auth('wang911017');
        $conn = null;
        $redis_data = null;
        $count = 0;
        while (true){
            try {
                $conn = $this->restart($count, $conn);
                $count ++;
                $res = $redis->brPop(['order_queue'],0); //取出数据
                $data = json_decode($res[1],true);
                $redis_data = $res[1];
                $conn->getPdo()->beginTransaction();
                $inserted = $conn->table('order')->where('serial',$data['out_trade_no'])->field('id')->find();
                if (!$inserted){
                    $cache = $this->getCache($redis, $data['out_trade_no']);
                    $cache['pay']['create_time'] = date('Y-m-d H:i:s');
                    $cache['pay']['update_time'] = date('Y-m-d H:i:s');
                    $order_id = $conn->table('order')->insertGetId($cache['pay']); //生成订单
                    if ($order_id){
                        $conn->table('pay')->insert([ //流水
                            'serial'=> $data['transaction_id'],
                            'amount'=>$data['total_fee'],
                            'order_id'=>$order_id,
                            'auth_id'=>$cache['pay']['auth_id'],
                            'openid'=>$cache['openid'],
                            'trade_time'=>time(),
                            'create_time'=> date('Y-m-d H:i:s'),
                            'update_time'=> date('Y-m-d H:i:s'),
                        ]);
                        //生成订单地址
                        $address = $conn->table('user_address')->find($cache['address_id']);
                        $conn->table('order_address')->insert([
                            'order_id'=>$order_id,
                            'district_id'=>$address['district_id'],
                            'contact_name'=>$address['contact_name'],
                            'contact_phone'=>$address['contact_phone'],
                            'contact_address'=>$address['contact_address'],
                        ]);
                        //生成订单商品
                        $order_goods = [];
                        $goodsStock = $conn->table('goods_stock');
                        $goods = $conn->table('goods');
                        if ($cache['goods']['type'] == 'cart'){
                            //删除购物车
                            $carts = [];
                            foreach ($cache['goods']['data'] as $datum) {
                                $stock = $goodsStock->where('id',$datum['goods_stock_id'])->find();//修改库存
                                $goodsStock->where('id',$datum['goods_stock_id'])->setDec('stock',$datum['number']);
                                $goods->where('id',$stock['goods_id'])
                                    ->setInc('saled',$datum['number']);
                                $order_goods[] = [
                                    'goods_stock_id'=>$datum['goods_stock_id'],
                                    'number'=>$datum['number'],
                                    'order_id'=>$order_id
                                ];
                                array_push($carts,$datum['cart_id']);
                            }
                            $conn->table('cart')->delete($carts);
                            unset($carts);
                        }else{
                            $order_goods = [
                                'goods_stock_id'=>$cache['goods']['data']['goods_stock_id'],
                                'number'=>$cache['goods']['data']['number'],
                                'order_id'=>$order_id
                            ];
                            $stock = $goodsStock->where('id',$cache['goods']['data']['goods_stock_id'])->find();//修改库存
                            $goodsStock->where('id',$cache['goods']['data']['goods_stock_id'])
                                ->setDec('stock',$cache['goods']['data']['number']);
                            $goods->where('id',$stock['goods_id'])
                                ->setInc('saled',$cache['goods']['data']['number']);
                        }
                        $conn->table('order_goods')->insertAll($order_goods);
                        //修改用户消费次数 增加积分
                        $user = $conn->table('wx_auth')->where('id',$cache['pay']['auth_id'])
                            ->field('parent_id')->find();
                        $score = floor($data['total_fee']/100);
                        if ($user['parent_id']){
                            $parent_user_info = $conn->table('wx_user')->where('auth_id',$user['parent_id'])->find();
                            $parent_user_info['score'] += $score;
                            $parent_user_info['score_total'] += $score;
                            $parent_user_info['update_time'] += date('Y-m-d H:i:s');
                            $conn->table('wx_user')->where('id',$parent_user_info['id'])->update($parent_user_info);
                            unset($parent_user_info);
                        }
                        $user_info = $conn->table('wx_user')->where('auth_id',$cache['pay']['auth_id'])->find();
                        $user_info['consume_amount'] += $data['total_fee'];
                        $user_info['consume_count'] += 1;
                        $user_info['score'] += $score;
                        $user_info['score_total'] += $score;
                        $user_info['update_time'] += date('Y-m-d H:i:s');
                        $conn->table('wx_user')->where('id',$user_info['id'])->update($user_info);
                        unset($user_info);
                        //修改优惠券状态
                        $conn->table('receive')->where('id',$cache['coupon_id'])->setField('used',1);
                        $conn->getPdo()->commit();
                        unset($cache);unset($order_goods);unset($user);unset($stock);
                        unset($score);unset($data);unset($datum);unset($address);
                        $conn->free();$conn->close();
                    }
                }
            }catch (\Exception $e) {
                echo $e->getMessage();
                echo $e->getTraceAsString();
                if ($redis_data){
                    $redis->lPush('order_queue',$redis_data);
                }
                $conn->getPdo()->rollback();
                exit(0);
            }
        }
    }

    /**
     * @param $count
     * @param $conn
     * @return \think\db\Connection
     * @throws \think\Exception
     */
    protected function restart($count, $conn)
    {
        if (memory_get_usage() > 50 * 1024 * 1024) {
            exit(0);
        } else {
            sleep(2);
        }
        if ($count >= 10) {
            exit(0);
        }

        if (!$conn) {
            $conn = Db::connect([], true);
        }
        return $conn;
    }

    /**
     * @param Redis $redis
     * @param $key
     * @return mixed
     */
    protected function getCache($redis, $key)
    {
        $cache = $redis->get($key);
        $cache = 0 === strpos($cache, 'think_serialize:') ? unserialize(substr($cache, 16)) : $cache;
        return $cache;
    }
}