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

    public function prePay(){
        $key = input('key');
        $address_id = input('address_id');
        $remark = input('remark');
        $cache = Cache::pull($key);
        if (!$cache){
            return CodeResponse::fail(CodeResponse::CODE_SYSTEM_ERROR,null,'系统异常');
        }
        $auth_id = $this->getUid();
        $openid = $this->getOpenid();
        $result = $this->service->pay($cache,$auth_id,$openid,$address_id,$remark);
        return CodeResponse::format($result);
    }

    public function notify(){
        $pay = new Wxpay(config('wx'));
        $res = $pay->verify_notify();
        Log::write($res,'NOTIFY_CHECK',true);
        if (!$res['error']){
            $this->service->notify($res['data']);
            return "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
        }
    }
}