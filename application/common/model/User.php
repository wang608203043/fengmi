<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/5
 * Time: 18:12
 */

namespace app\common\model;

/**
 * Class User
 * @package app\common\model
 * @property $id
 * @property $consume_amount
 * @property $consume_count
 * @property $score
 * @property $score_total
 * @property $create_time
 * @property $update_time
 */
class User extends BaseModel
{
    protected $table = 'wx_user';
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