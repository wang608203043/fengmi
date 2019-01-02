<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/5
 * Time: 18:34
 */

namespace app\common\model;

/**
 * Class Cart
 * @package app\common\model
 * @property $id
 * @property $auth_id
 * @property $goods_stock_id
 * @property $number
 * @property $create_time
 * @property $update_time
 */
class Cart extends BaseModel
{
    protected $table = 'cart';
    protected $autoWriteTimestamp = 'datetime';

    public function user(){
        return $this->belongsTo('Auth','auth_id','id');
    }

    public function goodsStock(){
        return $this->belongsTo('GoodsStock','goods_stock_id','id');
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