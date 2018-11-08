<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/24
 * Time: 14:16
 */

namespace app\common\model;

/**
 * Class OrderAddress
 * @package app\common\model
 * @property $id
 * @property $order_id
 * @property $district_id
 * @property $contact_name
 * @property $contact_address
 * @property $contact_phone
 */
class OrderAddress extends BaseModel
{
    protected $table = 'order_address';
    public function goodsOrder(){
        return $this->hasOne('Order','order_id','id');
    }

    /**
     * @param $id
     * @param $field_values
     * @return false|int|$this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function saveOrUpdate($id,$field_values){
        if ($id){
            $model = $this->findById($id);
        }else{
            $model = new self();
        }
        foreach ($field_values as $field=>$field_value) {
            $model->$field = $field_value;
        }

        if ($model->save()){
            $model = $this->findById($model->id);
        }
        return $model;
    }

}