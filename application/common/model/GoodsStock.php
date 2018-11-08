<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/5
 * Time: 18:18
 */

namespace app\common\model;

/**
 * Class GoodsStock
 * @package app\common\model
 * @property $id
 * @property $goods_id
 * @property $name
 * @property $stock
 * @property $price
 * @property $img_url
 * @property $attribute
 * @property $deleted
 * @property $create_time
 * @property $update_time
 *
 * @property Goods $goods
 */
class GoodsStock extends BaseModel
{
    protected $table = 'goods_stock';
    protected $autoWriteTimestamp = 'datetime';

    public function goods(){
        return $this->belongsTo('Goods','goods_id','id');
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