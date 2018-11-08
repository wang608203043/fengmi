<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/24
 * Time: 14:24
 */

namespace app\common\model;

/**
 * Class Pay
 * @package app\common\model
 * @property $id
 * @property $serial
 * @property $order_id
 * @property $auth_id
 * @property $amount
 * @property $channel
 * @property $openid
 * @property $trade_time
 * @property $deleted
 * @property $create_time
 * @property $update_time
 */
class Pay extends BaseModel
{
    protected $table = 'pay';
    protected $autoWriteTimestamp = 'datetime';

    public function user(){
        return $this->belongsTo('Auth','auth_id','id');
    }

    public function goodsOrder(){
        return $this->belongsTo('Order','order_id','id');
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