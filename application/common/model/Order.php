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

    const ORDER_PENDING_PAY = 0; //待付款
    const ORDER_PENDING_SEND = 1; //代发货
    const ORDER_RECEIVE = 2; //待收货
    const ORDER_COMMENT = 3; //待评价
    const ORDER_DONE = 4; //已完成
    const ORDER_CANCEL = 5; //取消订单

    public static $statusMap = [
        self::ORDER_PENDING_PAY,
        self::ORDER_PENDING_SEND,
        self::ORDER_RECEIVE,
        self::ORDER_COMMENT,
        self::ORDER_DONE,
        self::ORDER_CANCEL
    ];

    public static $statusTextMap = [
        self::ORDER_PENDING_PAY => '待付款',
        self::ORDER_PENDING_SEND => '待发货',
        self::ORDER_RECEIVE => '待收货',
        self::ORDER_COMMENT => '待评价',
        self::ORDER_DONE => '已完成',
        self::ORDER_CANCEL => '已取消',
    ];

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