<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/17
 * Time: 15:52
 */

namespace app\common\model;

/**
 * Class AdminUser
 * @package app\common\model
 * @property $id
 * @property $account
 * @property $password
 * @property $admin_name
 * @property $nick_name
 * @property $deleted
 * @property $create_time
 * @property $update_time
 */
class AdminUser extends BaseModel
{
    protected $table = 'admin_user';
    protected $autoWriteTimestamp = 'datetime';
    /**
     * @param $account
     * @param $md5
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findByAccount($account, $md5)
    {
        return $this->where(['deleted'=>self::$DELETED_FALSE,'account'=>$account,'password'=>$md5])->find();
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