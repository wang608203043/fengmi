<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/24
 * Time: 14:12
 */

namespace app\common\model;

/**
 * Class Order
 * @package app\common\model
 * @property $id
 * @property $serial
 * @property $auth_id
 * @property $amount
 * @property $track_no
 * @property $status
 * @property $remark
 * @property $commenting
 * @property $deleted
 * @property $create_time
 * @property $update_time
 *
 * @property Auth $user
 */
class Order extends BaseModel
{
    protected $table = 'order';
    protected $autoWriteTimestamp = 'datetime';

    public function user(){
        return $this->belongsTo('Auth','auth_id','id');
    }

    public function address(){
        return $this->hasOne('OrderAddress','order_id','id');
    }

    public function goodsStock(){
        return $this->belongsToMany('GoodsStock','order_goods','goods_stock_id','order_id');
    }

    public function pay(){
        return $this->hasOne('Pay','order_id','id');
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