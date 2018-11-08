<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/7
 * Time: 17:21
 */

namespace app\common\service;


use app\common\model\Cart;

class CartService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new Cart();
    }

    /**
     * thinkphp分页查询
     * @param array $where
     * @param array $order
     * @param int $listRow
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function baseList($where = [],$order = [],$listRow = 15){
        return $this->model->selectActiveByThinkPage($where,$order,$listRow);
    }

    /**
     * 普通分页
     * @param int $page
     * @param int $listRow
     * @param array $where
     * @param array $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function baseListByPage($page = 1, $listRow = 15, $where = [], $order = []){
        return $this->model->selectActiveByPage($page,$listRow,$where,$order);
    }

    /**
     * @param $id
     * @return $this|array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getById($id){
        return $this->model->findById($id);
    }

    /**
     * @param $id
     * @param $field_values
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function baseSave($id, $field_values){
        return $this->model->saveOrUpdate($id,$field_values);
    }

    /**
     * 软删除
     * @param $id
     * @return int
     */
    public function delete($id){
        return $this->model->deleteById($id);
    }

    /**
     * 强制删除
     * @param $id
     * @return int
     */
    public function deleteForce($id)
    {
        return $this->model->where('id',$id)->delete();
    }

    public function change($id,$field, $value)
    {
        return $this->model->updateField($id,$field,$value);
    }

    /**
     * @param $auth_id
     * @param $page
     * @param int $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCarts($auth_id, $page, $listRow = 10)
    {
        $carts = $this->model->where(['auth_id'=>$auth_id])->page($page,$listRow)->order('create_time desc')->select();
        $list = [];
        foreach ($carts as $cart) {
            $list[] = [
                'id'=>$cart->id,
                'gname'=>$cart->goodsStock->goods->gname,
                'img_url'=>$cart->goodsStock->img_url,
                'price'=>$cart->goodsStock->price,
                'attribute'=>$cart->goodsStock->attribute,
                'number'=>$cart->number,
            ];
        }
        return $list;
    }
}