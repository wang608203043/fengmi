<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function build_order_no(){
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $orderSn = $yCode[intval(date('Y')) - 2018] .
        strtoupper(dechex(date('m'))) .
        date('d') .
        substr(time(), -5) .
        substr(microtime(), 2, 5) .
        sprintf('%02d', rand(0, 99));
    return $orderSn;
}

function build_member_no($sn){
    \think\Log::write($sn,'log_sn',true);
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $memberSn = $sn.$yCode[intval(date('Y')) - 2018] .
        date('m') .
        date('d') .
        substr(time(), -5);
    return $memberSn;
}
/**
 * @param string $url
 * @param string $type
 * @param  array $arr
 * @return mixed|array
 */
function http_curl($url,$type='get',$arr=[]){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
    if (strtolower($type)==='post'){
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
    }
    $res = curl_exec($ch);
    $res = json_decode($res,true);
    if (curl_errno($ch)){
        $res = curl_error($ch);
    }
    curl_close($ch);
    return $res;
}

function base64_image_content($base64_image_content,$path,$uri='/upload/'){
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];
        $new_file = $path.date('Ymd',time())."/";
        if(!file_exists($new_file)){
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }
        $name = md5(time().rand(10000,999999));
        $new_file = $new_file.$name.".{$type}";
        $uri = $uri.date('Ymd',time())."/".$name.".{$type}";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            return $uri;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/**
 * 二维数组根据某一字段排序
 * @param $arrays
 * @param $sort_key
 * @param string $sort_order
 * @param string $sort_type
 * @return mixed
 */
function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC){
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}

function rand_num($len){
    $num_str = [0,1,2,3,4,5,6,7,8,9];
    $code = '';
    for ($i=0;$i<$len;$i++){
        $code.= $num_str[rand(0,9)];
    }
    return $code;
}

function createQRCode($text){
    $res = file_get_contents('http://qr.liantu.com/api.php?text='.$text);
    !file_exists(ROOT_PATH.'public/static/qr/'.date('Ymd'))&&
    mkdir(ROOT_PATH.'public/static/qr/'.date('Ymd'));
    $save_name = '/static/qr/'.date('Ymd').'/'.md5(time()).'.png';
    file_put_contents(ROOT_PATH.'public'.$save_name,$res);
    return $save_name;
}

function array_unset_tt($arr,$key){
    //建立一个目标数组
    $res = array();
    foreach ($arr as $value) {
        //查看有没有重复项
        if(isset($res[$value[$key]])){
            unset($value[$key]);  //有：销毁
        }else{
            $res[$value[$key]] = $value;
        }
    }
    return $res;
}

function uniqueNumber()
{
    return date('YmdHis') . substr(implode(NULL, array_map('ord', str_split(strrev(substr(uniqid(), 7, 13)), 1))), 0, 8);
}

function createNonceStr( $length = 32 )
{
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $str ="";
    for ( $i = 0; $i < $length; $i++ ) {
        $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
    }
    return $str;
}

function uniqueStr(){
    return md5(uniqid(microtime(true),true));
}