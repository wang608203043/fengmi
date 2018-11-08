<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/6
 * Time: 14:04
 */

namespace app\common\service;


use app\common\model\AdminUser;
use app\common\model\BaseModel;

class AdminUserService extends BaseService
{
    protected $model;
    public function __construct()
    {
        $this->model = new AdminUser();
    }

    /**
     * @param $account
     * @param $password
     * @return array|false|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAdminByLogin($account, $password){
        $admin = $this->model->findByAccount($account,md5($password));
        if ($admin){
            session('admin_user',$admin);
            return $admin;
        }
        return null;
    }

    public function modifyPassword($id, $password)
    {
        $this->model->updateField($id,'password',$password);
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
     * 软删除
     * @param $id
     * @return int
     */
    public function delete($id){
        return $this->model->deleteById($id);
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
     * 强制删除
     * @param $id
     * @return int
     */
    public function deleteForce($id)
    {
        return $this->model->where('id',$id)->delete();
    }
}