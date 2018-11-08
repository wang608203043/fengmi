<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2018/8/20
 * Time: 11:05
 */

namespace app\api\controller;

use app\common\component\CodeResponse;
use think\Cache;
use think\Log;

class Utils extends BaseController
{
    /**
     * 文件上传
     * 参数  filename  文件名  fileType 文件类型（base64,file）  file
     */
    public function upload(){
        $fileType = input('fileType');
        $filename = input('filename');
        $file_path = '';
        if ($fileType == 'base64'){
            $file_path = base64_image_content(input($filename),UPLOAD_PATH);
        }elseif ($fileType == 'file'){
            $file = $this->request->file($filename);
            $info = $file->move(UPLOAD_PATH);
            $file_path = '/upload/'.$info->getSaveName();
        }elseif ($file = $this->request->file('file')){
            $info = $file->move(UPLOAD_PATH);
            if ($info){
                $file_path = '/upload/'.$info->getSaveName();
            }else{
                return CodeResponse::fail(CodeResponse::CODE_ERROR_UPLOAD);
            }
        }
        return CodeResponse::format(['upload_url'=>$file_path]);
    }

    /**
     * 获取短信验证码
     * @return \think\response\Json
     */
    public function getSmsCode(){
        $phone = input('phone');
        $code = rand_num(4);
        $code_key = md5(time().''.$code);
        Cache::set($code_key,$code,3000);
        $post_data['accountSid'] = config('sms.accountSid');
        $post_data['templateid'] = config('sms.templateid');
        $post_data['timestamp'] = date('YmdHis');
        $post_data['to'] = $phone;
        $post_data['param'] = $code.',5';
        $post_data['sig'] = md5($post_data['accountSid'].config('sms.auth_token').$post_data['timestamp']);
        $res = http_curl(config('sms.url'),'post',$post_data);
        Log::write($res,'sms_log',true);
        if ($res['respCode']!='00000'){
            CodeResponse::error(CodeResponse::CODE_ERROR_SMS,$res);
        }
        return CodeResponse::format($code_key);
    }
}