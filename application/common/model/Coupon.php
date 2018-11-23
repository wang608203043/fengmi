<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/14
 * Time: 16:40
 */

namespace app\common\model;


class Coupon extends BaseModel
{
    protected $table = 'coupon';
    protected $autoWriteTimestamp = 'datetime';

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