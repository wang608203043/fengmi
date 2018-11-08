<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/18
 * Time: 15:13
 */

namespace app\common\model;

/**
 * Class Goods
 * @package app\common\model
 * @property $id
 * @property $category_id
 * @property $gname
 * @property $price
 * @property $img_url
 * @property $images
 * @property $content
 * @property $is_down
 * @property $is_recommend
 * @property $deleted
 * @property $create_time
 * @property $update_time
 */
class Goods extends BaseModel
{
    protected $table = 'goods';
    protected $autoWriteTimestamp = 'datetime';

    const RECOMMEND_GOODS = 1;//推荐
    const COMMON_GOODS = 0;

    public function category(){
        return $this->belongsTo('Category','category_id','id');
    }

    public function goodsComment(){
        return $this->hasMany('GoodsComment','goods_id','id');
    }

    public function stock(){
        return $this->hasMany('GoodsStock','goods_id','id');
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