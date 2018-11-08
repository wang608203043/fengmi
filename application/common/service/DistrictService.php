<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/23
 * Time: 16:37
 */

namespace app\common\service;


use app\common\model\District;

class DistrictService
{
    /**
     * 获取全部省份
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProvinces(){
        return (new District())->province();
    }

    /**
     * @param null $province_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCities($province_id = null){
        return (new District())->city($province_id);
    }

    /**
     * @param $city_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAreas($city_id){
        return (new District())->area($city_id);
    }

    /**
     * @param $district_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFullAddress($district_id){
        return (new District())->getFullAddress($district_id);
    }
}