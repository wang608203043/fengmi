<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/14
 * Time: 16:41
 */

namespace app\common\model;


class Receive extends BaseModel
{
    protected $table = 'receive';
    protected $autoWriteTimestamp = 'datetime';

    public function user(){
        return $this->belongsTo('Auth','auth_id','id');
    }
    public function coupon(){
        return $this->belongsTo('Coupon','coupon_id','id');
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