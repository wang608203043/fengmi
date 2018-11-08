<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/15
 * Time: 16:18
 */

namespace app\api\controller;


use think\Cache;
use think\Controller;
use think\Log;
use think\Request;

class BaseController extends Controller
{
    const SESSION_SALT = 'salt_session_base'; //小程序
    public $session_key = null;
    public $service;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->session_key = $request->header('SES-KEY');
    }

    public function getPostInput($key,$default = null){
        return $this->request->post($key,$default);
    }

    public function getUid(){
        $cache = Cache::get($this->session_key);
        return $cache['uid'];
    }

    public function getOpenid(){
        $cache = Cache::get($this->session_key);
        return $cache['openid'];
    }
}