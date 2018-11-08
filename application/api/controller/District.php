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
        if ($province_id){
            $list = (new DistrictService())->getCities($province_id);
        }elseif ($city_id){
            $list = (new DistrictService())->getAreas($city_id);
        }else{
            $list = (new DistrictService())->getProvinces();
        }
        return CodeResponse::format($list);
    }
}