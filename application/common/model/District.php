<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/23
 * Time: 16:17
 */

namespace app\common\model;

/**
 * Class District
 * @package app\common\model
 * @property $id
 * @property $code
 * @property $name
 * @property $province_id
 * @property $city_id
 * @property $number
 * @property $level
 */
class District extends BaseModel
{
    protected $table = 'district';

    /**
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function province(){
        return $this->where('province_id',0)->select();
    }

    /**
     * @param null $province_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function city($province_id = null){
        $where = ['city_id'=>0];
        if ($province_id){
            $where['province_id'] = $province_id;
        }
        return $this->where($where)->select();
    }

    /**
     * @param $district_id
     * @return $this|array|false|\PDOStatement|string|\think\Model|District
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getProvinceById($district_id){
        $district = $this->findById($district_id);
        $province = $this->findById($district->province_id);
        return $province;
    }

    /**
     * @param $district_id
     * @return $this|array|false|\PDOStatement|string|\think\Model|District
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCityById($district_id){
        $district = $this->findById($district_id);
        $city = $this->findById($district->city_id);
        return $city;
    }

    /**
     * @param $district_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getFullAddress($district_id){
        $district = $this->findById($district_id);
        $city = $this->findById($district->city_id);
        $province = $this->findById($district->province_id);
        return ['province'=>$province,'city'=>$city,'area'=>$district];
    }

    /**
     * @param $city_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function area($city_id)
    {
        $where['city_id'] = $city_id;
        return $this->where($where)->select();
    }
}