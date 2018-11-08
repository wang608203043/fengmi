<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/16
 * Time: 14:08
 */

namespace app\common\component;


use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use think\Config;
use think\Request;

class Token
{
    protected $payload = [];
    protected  $iss = 'sys_';
    protected  $params = [];
    protected  $token_key = 'x-token-key';
    protected  $expire = 7200; //过期时间
    protected  $white_space = null;

    /**
     * Token constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        $this->expire = config('token.expire')?:$this->expire;
        $this->token_key = config('token.token_key')?:$this->token_key;
        $this->params = array_merge($this->params,$params)?:[];
        $this->iss = config('token.iss')?:$this->iss;
        $this->white_space = config('white_space');
    }

    /**
     * 生成token
     * @param array $params
     */
    public function createToken($params = []){
        $this->params = array_merge($this->params,$params);
        $this->payload = [
            'iss'=>$this->iss,
            'iat'=>time(),
            'exp'=>time() + $this->expire,
            'rand'=> rand(1000000,9999999)
        ];
        $this->payload = array_merge($this->payload,$this->params);
        $jwt = JWT::encode($this->payload,$this->token_key);
        header('X-TOKEN: '.$jwt);
    }

    /**
     *  解析token
     * @param Request $request
     * @return void
     */
    public function check_token($request){
        if (Config::get('token.on')){
            $route = $request->module().'/'.$request->controller().'/'.$request->dispatch()['module'][2];
            if (is_array($this->white_space) && !in_array($route,$this->white_space)){
                try {
                    $token = request()->header('X-TOKEN');
                    JWT::decode($token,$this->token_key,array('HS256'));
                } catch (ExpiredException $exception) {
                    CodeResponse::error(CodeResponse::CODE_ERROR_TOKEN_EXPIRE);
                }
            }
        }
    }
}