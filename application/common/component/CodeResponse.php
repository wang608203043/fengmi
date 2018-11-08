<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/16
 * Time: 10:04
 */

namespace app\common\component;


use think\Response;

class CodeResponse
{
    //1-系统 00-模块  00-业务
    const CODE_SUCCESS = 10000;
    const CODE_PARAMS_ERROR = 10001;
    const CODE_CUSTOM_ERROR = 10097;
    const CODE_REPEAT_ERROR = 10098;
    const CODE_SYSTEM_ERROR = 10099;
    //手机号错误
    const CODE_ERROR_PHONE = 11001;
    //密码格式不正确
    const CODE_ERROR_PASSWORD = 11002;
    //短信验证码不正确
    const CODE_ERROR_PHONE_CODE = 11003;
    //手机号已注册
    const CODE_ERROR_PHONE_USED = 11004;
    //手机号或密码错误
    const CODE_ERROR_PHONE_PASSWORD = 11005;
    //用户未登录
    const CODE_ERROR_NEED_LOGIN = 11006;
    //手机号不存在
    const CODE_ERROR_PHONE_UNKNOWN = 11007;
    //账号或密码错误
    const CODE_ERROR_ACCOUNT_PASSWORD = 11008;
    //账号被冻结
    const CODE_ERROR_ACCOUNT_FROZEN = 11009;
    //活动不存在
    const CODE_ERROR_ACTIVITY_UNKNOWN = 12001;
    //活动未开始
    const CODE_ERROR_ACTIVITY_NOT_START = 12002;
    //活动已结束
    const CODE_ERROR_ACTIVITY_END = 12003;
    //活动奖品无库存
    const CODE_ERROR_ACTIVITY_NOT_STOCK = 12004;
    //已经参加过
    const CODE_ERROR_ACTIVITY_HAS_JOIN = 12005;
    //优惠券选择有误
    const CODE_ERROR_ACTIVITY_NOT_MATCH = 12006;
    //商品已下线
    const CODE_ERROR_GOOD_DOWN = 13001;
    //商品库存不足
    const CODE_ERROR_GOOD_NOT_ENOUGH = 13002;
    //学校还未开通
    const CODE_ERROR_SCHOOL_NOT_OPEN = 14001;
    //订单状态不正确
    const CODE_ERROR_ORDER_STATUS = 15001;
    //支付渠道不支持
    const CODE_ERROR_PAY_CHANNEL = 15002;
    //支付金额有误
    const CODE_ERROR_PAY_AMOUNT = 15003;
    //渠道异常,支付失败
    const CODE_ERROR_PAY_OTHER = 15004;
    //融云返回异常
    const CODE_ERROR_RY_ERROR = 160001;
    //token过期
    const CODE_ERROR_TOKEN_EXPIRE = 17001;
    //上传错误
    const CODE_ERROR_UPLOAD = 17002;
    //获取验证码异常
    const CODE_ERROR_SMS = 17003;

    public static $code_msg_map = [
        self::CODE_SUCCESS => '成功',
        self::CODE_CUSTOM_ERROR => '请检查您的输入是否正确',
        self::CODE_PARAMS_ERROR => '参数错误',
        self::CODE_REPEAT_ERROR => '请勿重复操作',
        self::CODE_SYSTEM_ERROR => '系统异常，请稍后再试',
        self::CODE_ERROR_PHONE => '请输入正确的手机号',
        self::CODE_ERROR_PASSWORD => '请输入6-16位数字或字母的密码',
        self::CODE_ERROR_PHONE_CODE => '请输入正确的短信验证码',
        self::CODE_ERROR_PHONE_USED => '手机号已注册',
        self::CODE_ERROR_PHONE_PASSWORD => '你输入的密码和手机号不匹配',
        self::CODE_ERROR_ACCOUNT_PASSWORD => '账号或密码错误',
        self::CODE_ERROR_NEED_LOGIN => '请登录后再来',
        self::CODE_ERROR_PHONE_UNKNOWN => '手机号未注册',
        self::CODE_ERROR_ACTIVITY_UNKNOWN => '优惠活动不存在或已下线',
        self::CODE_ERROR_ACTIVITY_NOT_START => '活动还未开始，请耐心等待',
        self::CODE_ERROR_ACTIVITY_END => '活动已结束，请下次参与',
        self::CODE_ERROR_ACTIVITY_NOT_STOCK => '活动太火爆，已被一抢而空',
        self::CODE_ERROR_ACTIVITY_HAS_JOIN => '您已经领取过啦，请勿重复领取',
        self::CODE_ERROR_GOOD_DOWN => '您查看的商品已下线',
        self::CODE_ERROR_GOOD_NOT_ENOUGH => '您选择的商品库存不足',
        self::CODE_ERROR_SCHOOL_NOT_OPEN => '您选择的学校暂未开通',
        self::CODE_ERROR_ACTIVITY_NOT_MATCH => '您选择的优惠券已失效',
        self::CODE_ERROR_ORDER_STATUS => '您的订单状态不正确，请重试',
        self::CODE_ERROR_PAY_CHANNEL => '暂不支持该支付渠道',
        self::CODE_ERROR_PAY_AMOUNT => '请重新选择您的支付金额',
        self::CODE_ERROR_PAY_OTHER => '支付渠道系统繁忙，请稍后重试',
        self::CODE_ERROR_RY_ERROR => '第三方系统异常，请稍后重试',
        self::CODE_ERROR_TOKEN_EXPIRE => '令牌已过期',
        self::CODE_ERROR_ACCOUNT_FROZEN => '账号被冻结',
        self::CODE_ERROR_UPLOAD =>'上传出错',
        self::CODE_ERROR_SMS =>'获取短信验证码异常',
    ];

    /**
     * 获取映射消息
     * @param $code
     * @return mixed
     */
    public static function getMessage($code)
    {
        return self::$code_msg_map[$code];
    }

    /**
     * 获取消息list
     * @param $code
     * @return array
     */
    public static function getMap($code)
    {
        return [self::getMessage($code), $code];
    }

    /**
     * @param null $data
     * @param int $code
     * @return \think\response\Json
     */
    public static function format($data = null, $code = self::CODE_SUCCESS)
    {
        return json([
            'code' => $code,
            'message' => self::getMessage($code),
            'data' => isset($data) ? $data : []
        ]);
    }

    /**
     * @param int $code
     * @param null $data
     * @param null $message
     * @return \think\response\Json
     */
    public static function fail($code = self::CODE_SYSTEM_ERROR, $data = null, $message = null)
    {
        return json([
            'code' => $code,
            'message' => $message ? $message : self::getMessage($code),
            'data' => isset($data) ? $data : []
        ]);
    }

    /**
     * 格式化返回
     * @param int $code
     * @param mixed $data
     * @param string $message
     * @return void
     */
    public static function error($code = self::CODE_SYSTEM_ERROR, $data = null, $message = null)
    {
        $result = [
            'code' => $code,
            'message' => $message ? $message : self::getMessage($code),
            'data' => isset($data) ? $data : []
        ];
        Response::create($result,'json',200)->send();
        exit();
    }
}