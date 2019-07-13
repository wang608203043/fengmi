<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/23
 * Time: 15:28
 */

namespace app\common\model;

/**
 * Class GoodsComment
 * @package app\common\model
 * @property $id
 * @property $goods_id
 * @property $auth_id
 * @property $comment
 * @property $deleted
 * @property $create_time
 * @property $update_time
 */
class GoodsComment extends BaseModel
{
    protected $table = 'goods_comment';
    protected $autoWriteTimestamp = 'datetime';

    public function user(){
        return $this->belongsTo('Auth','auth_id','id');
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