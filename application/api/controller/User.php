<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/18
 * Time: 16:07
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\AuthService;
use think\Cache;
use think\helper\Hash;
use think\Request;

class User extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new AuthService();
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSessionId(){
        $code = input('code');
        $api = 'https://api.weixin.qq.com/sns/jscode2session?appid='.config('wx.app_id').'&secret='.config('wx.app_secret').'&js_code='.$code.'&grant_type=authorization_code';
        $res = http_curl($api);
        if (isset($res['openid'])){
            $user = $this->service->getByOpenid($res['openid']);
            $cache = ['openid'=>$res['openid'],'sessionKey'=>$res['session_key']];
            if ($user){
                $cache['uid'] = $user->id;
            }else{
                $parent_id = input('parent_id',0);
                $uid = $this->service->baseSave(null,['openid'=>$res['openid'],'parent_id'=>$parent_id]);
                $this->service->createUser($uid);
                $cache['uid'] = $uid;
            }
            $cache_key = crypt($res['session_key'].uniqid(),self::SESSION_SALT);
            Cache::set($cache_key,$cache,35*24*3600);
            return CodeResponse::format(['session_key'=>$cache_key]);
        }else{
            CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR,$res,'微信登陆异常');
        }
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function updateUser(){
        $nick_name = input('nick_name');
        $img_url = input('img_url');
        $uid = $this->getUid();
        $res = $this->service->baseSave($uid,['nick_name'=>$nick_name,'img_url'=>$img_url]);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAddressList(){
        $auth_id = $this->getUid();
        $list = $this->service->getAddressList($auth_id);
        return CodeResponse::format($list);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getParentId(){
        $auth_id = $this->getUid();
        $extend = $this->service->getUserExtend($auth_id);
        return CodeResponse::format(['parent_id'=>$extend->id]);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCoupon(){
        $auth_id = $this->getUid();
        $list = $this->service->getActiveCoupon($auth_id);
        return CodeResponse::format($list);
    }
}