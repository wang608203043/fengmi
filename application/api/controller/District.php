<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/23
 * Time: 16:46
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\DistrictService;

class District extends BaseController
{
    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        $province_id = input('province_id');
        $city_id = input('city_id');
        if (!$city_id && !$province_id){
            $list = (new DistrictService())->getProvinces();
        }elseif ($province_id){
            $list = (new DistrictService())->getCities($province_id);
        }else{
            $list = (new DistrictService())->getAreas($city_id);
        }
        return CodeResponse::format($list);
    }
}