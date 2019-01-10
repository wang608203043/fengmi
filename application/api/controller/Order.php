<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/7
 * Time: 18:08
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\OrderService;
use think\Cache;
use think\Log;
use think\Request;
use YYHwxpay\Wxpay;

class Order extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new OrderService();
    }

    /**
     * 结算
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function settle(){
        $cart_ids = input('cart_ids');
        $goods_stock_id = input('goods_stock_id');
        $cache_key = md5(uniqid().time());
        if ($goods_stock_id){
            $number = input('number');
            $data = $this->service->getStock($goods_stock_id,$number);
            Cache::set($cache_key,['data'=>$data,'type'=>'stock'],900);
        }elseif ($cart_ids){
            if (is_string($cart_ids)){
                $cart_ids = explode(',',trim($cart_ids,','));
            }elseif (is_array($ids = input('cart_ids/a'))){
                $cart_ids = $ids;
            }else{
                return CodeResponse::fail(CodeResponse::CODE_SYSTEM_ERROR,['data'=>$cart_ids],'购物车参数错误');
            }
            $data = $this->service->getCarts($cart_ids);
            Cache::set($cache_key,['data'=>$data,'type'=>'cart'],900);
        }else{
            return CodeResponse::fail();
        }
        return CodeResponse::format(['data'=>$data,'key'=>$cache_key]);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function prePay(){
        $key = input('key');
        $address_id = input('address_id');
        $remark = input('remark');
        $coupon_id = input('coupon_id');
        $cache = Cache::pull($key);
        if (!$cache){
            return CodeResponse::fail(CodeResponse::CODE_SYSTEM_ERROR,null,'系统异常');
        }
        $auth_id = $this->getUid();
        $openid = $this->getOpenid();
        $result = $this->service->pay($cache,$auth_id,$openid,$address_id,$remark,$coupon_id);
        return CodeResponse::format($result);
    }

    /**
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notify(){
        $pay = new Wxpay(config('wx'));
        $res = $pay->verify_notify();
        Log::write($res,'NOTIFY_CHECK',true);
        if (!$res['error']){
            $res = $this->service->notify($res['data']);
            if ($res){
                return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            }
        }
        return CodeResponse::fail();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        $auth_id = $this->getUid();
        $status = input('status');
        $page = input('page');
        $list = (new OrderService())->getList($auth_id,$status,$page);
        return CodeResponse::format($list);
    }

    public function confirm(){
        $order_id = input('order_id');
        $res = (new OrderService())->confirm($order_id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function comment(){
        $data['goods_id'] = input('goods_id');
        $data['content'] = input('content');
        $data['auth_id'] = $this->getUid();
        $res = (new OrderService())->comment($data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }
}