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
 * Class Auth
 * @package app\common\model
 * @property $id
 * @property $img_url
 * @property $nick_name
 * @property $openid
 * @property $create_time
 * @property $update_time
 *
 *
 * @property User $user
 */
class Auth extends BaseModel
{
    protected $table = 'wx_auth';
    protected $autoWriteTimestamp = 'datetime';

    public function user(){
        return $this->hasOne('User','auth_id','id');
    }

    public function address(){
        return $this->hasMany('UserAddress','auth_id','id');
    }

    public function receive(){
        return $this->hasMany('Receive','auth_id','id');
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
            $model->data($field_values);
        }
        if ($model->save()){
            $model = $this->findById($model->id);
        }
        return $model;
    }
}