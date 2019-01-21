<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/14
 * Time: 17:30
 */

namespace app\common\service;


use app\common\component\CodeResponse;
use app\common\model\BaseModel;
use app\common\model\IntegralOrder;
use app\common\model\Receive;
use app\common\model\User;
use app\common\model\UserAddress;

class IntegralOrderService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new IntegralOrder();
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
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function baseListByPage($page = 1, $listRow = 15, $where = [], $order = [],$field = '*'){
        return $this->model->selectActiveByPage($page,$listRow,$where,$order,$field);
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

    public function change($id,$field, $value)
    {
        return $this->model->updateField($id,$field,$value);
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

    /**
     * @param $auth_id
     * @param $integral_id
     * @param $address_id
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function createOrder($auth_id, $integral_id, $address_id)
    {
        $this->model->startTrans();
        try {
            $integral = $this->model->integral()->where(['id'=>$integral_id])->find();
            $user = (new User())->where(['auth_id'=>$auth_id])->find();
            if ($integral && $user){
                if ($user->score < $integral->price || $integral->stock = 0){
                    CodeResponse::error(CodeResponse::CODE_PARAMS_ERROR,null,'积分或库存不足,无法兑换');
                }
                if ($integral->genre == 1){ //优惠券商品
                    (new Receive())->saveOrUpdate(null,[
                        'coupon_id'=>$integral->coupon_id,
                        'auth_id'=>$auth_id,
                        'code' => uniqueStr()
                    ]);
                    $this->model->saveOrUpdate(null,[
                        'serial'=> uniqueNumber(),
                        'auth_id'=>$auth_id,
                        'integral_id'=>$integral_id,
                        'amount'=>$integral->price,
                        'status'=>2,
                        'genre'=>1
                    ]);
                }else { //实物商品
                    $address = (new UserAddress())->findById($address_id);
                    $this->model->saveOrUpdate(null,[
                        'serial'=> uniqueNumber(),
                        'auth_id'=>$auth_id,
                        'integral_id'=>$integral_id,
                        'amount'=>$integral->price,
                        'genre'=>2,
                        'district_id'=>$address->district_id,
                        'contact_name'=>$address->contact_name,
                        'contact_phone'=>$address->contact_phone,
                        'contact_address'=>$address->contact_address,
                    ]);
                }
                $user->score -= $integral->price;
                $integral->stock -= 1;
                $integral->saled += 1;
                $user->save();
                $integral->save();
                $this->model->commit();
            }else{
                CodeResponse::error(CodeResponse::CODE_PARAMS_ERROR,null,'系统异常');
            }
        } catch (\Exception $exception) {
            $this->model->rollback();
            CodeResponse::error(CodeResponse::CODE_PARAMS_ERROR,null,$exception->getMessage());
        }
        return true;
    }

    /**
     * @param $auth_id
     * @param $page
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderList($auth_id, $page)
    {
        $list = $this->model->with('integral')->where(['auth_id'=>$auth_id,'deleted'=>IntegralOrder::$DELETED_FALSE])
            ->order(['status'=>'asc','create_time'=>'desc'])->page($page,5)->select();
        return $list;
    }
}