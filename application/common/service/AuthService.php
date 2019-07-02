<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/7
 * Time: 15:42
 */

namespace app\common\service;


use app\common\component\CodeResponse;
use app\common\model\Auth;
use app\common\model\BaseModel;
use app\common\model\District;
use app\common\model\User;

class AuthService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new Auth();
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
     * @param $openid
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByOpenid($openid)
    {
        return $this->model->where('openid',$openid)->find();
    }

    /**
     * @param $auth_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAddressList($auth_id)
    {
        $auth = $this->model->findById($auth_id);
        $addresses = $auth->address()->where(['deleted'=>BaseModel::$DELETED_FALSE])->select();
        $list = [];
        $district = new District();
        foreach ($addresses as $address) {
            $detail = $district->getFullAddress($address->district_id);
            $list[] = [
                'id'=>$address->id,
                'contact_name'=>$address->contact_name,
                'contact_phone'=>$address->contact_phone,
                'contact_address'=>$address->contact_address,
                'district_id'=>$address->district_id,
                'detail'=> $detail
            ];
        }
        return $list;
    }

    public function createUser($auth_id){
        return (new User())->save(['auth_id'=>$auth_id]);
    }

    /**
     * @param $auth_id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserExtend($auth_id){
        return $this->model->user()->where('auth_id',$auth_id)->find();
    }

    /**
     * @param $auth_id
     * @param $integral
     * @param $type
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changeIntegral($auth_id, $integral, $type)
    {
        $user = $this->model->user()->where('auth_id',$auth_id)->find();
        if ($type == 1){
            $user->score += $integral;
            $user->score_total += $integral;
        }else{
            if ($user->score < $integral){
                CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR,null,'用户可用积分少于'.$integral.',操作失败');
            }
            $user->score -= $integral;
            $user->score_total -= $integral;
        }
        return $user->save();
    }

    /**
     * @param $auth_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getActiveCoupon($auth_id)
    {
        $data = [];
        $list = $this->model->receive()->where(['used'=>0,'auth_id'=>$auth_id])->with('coupon')
            ->order('create_time desc')->select();
        foreach ($list as $item) {
            $data[] = [
                'id'=>$item->id,
                'title'=>$item->coupon->title,
                'amount'=> $item->coupon->amount,
                'condition'=> $item->coupon->condition,
                'create_time'=> $item->create_time
            ];
        }
        return $data;
    }

    public function getIntegral($auth_id)
    {
        return $this->model->user()->where(['auth_id'=>$auth_id])->value('score',0);
    }
}