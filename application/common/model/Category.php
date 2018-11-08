<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/17
 * Time: 16:17
 */

namespace app\common\model;

/**
 * Class Category
 * @package app\common\model
 * @property $id
 * @property $cname
 * @property $img_url
 * @property $parent_id
 * @property $sort
 * @property $deleted
 * @property $create_time
 * @property $update_time
 */
class Category extends BaseModel
{
    protected $table = 'category';
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