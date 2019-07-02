<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/15
 * Time: 16:19
 */

namespace app\common\model;


use think\Model;

/**
 * Class BaseModel
 * @package app\common\model
 * @property $id
 */
class BaseModel extends Model
{
    public static $DELETED_TRUE = 1; //删除
    public static $DELETED_FALSE = 0; //未删除
    public static $ON_LINE = 0;  //上架
    public static $DOWN_LINE = 1; //下架

    public function initialize()   //注册事件
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        self::event('before_insert',function ($model){
//            if (isset($model->create_time)){
//                $model->create_time = date('Y-m-d H:i:s');
//            }
            //$this->fieldFilter($model);
        });
        self::event('before_update',function ($model){
            //$this->fieldFilter($model);
        });
    }

    /**
     * 过滤空字段,保留原值或默认值
     * @param Model $model
     */
    protected function fieldFilter($model){
        $data = $model->getData();
        foreach ($data as $key=>$item) {
            if ($this->isEmpty($item)){
                unset($data[$key]);
            }
        }
        $model->data($data);
    }

    protected function isEmpty($value)
    {
        return $value === false || $value === '' || $value === [] || $value === null || is_string($value) && trim($value) === '';
    }

    /**
     * 删除
     * @param integer|array $id
     * @return int
     */
    public function deleteById($id){
        if (is_array($id)){
            return $this->whereIn('id',$id)->setField('deleted',1);
        }else {
            return $this->where('id',$id)->setField('deleted',1);
        }
    }

    /**
     * @param $id
     * @return array|false|\PDOStatement|string|Model|$this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findById($id){
        return $this->where('id',$id)->find();
    }

    /**
     * @param $where
     * @return array|false|\PDOStatement|string|Model|$this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findOne($where){
        return $this->where($where)->find();
    }


    /**
     * @param $phone
     * @return array|false|\PDOStatement|string|Model|$this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findByPhone($phone){
        return $this->where('phone',$phone)->find();
    }

    /**
     * @param $email
     * @return array|false|\PDOStatement|string|Model|$this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findByEmail($email){
        return $this->where('email',$email)->find();
    }

    /**
     * 查询未删除的数据
     * @param array $where
     * @param array $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function selectActiveAll($where = [],$order = []){
        return $this->where($where)->where('deleted',self::$DELETED_FALSE)->order($order)->select();
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
    public function selectActiveByPage($page = 1, $listRow = 15, $where = [], $order = [],$field = '*'){
        return $this->field($field)->where($where)->where('deleted',self::$DELETED_FALSE)->order($order)->page($page,$listRow)->select();
    }

    /**
     * thinkphp 分页查询
     * @param array $where
     * @param array $order
     * @param int $listRow
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function selectActiveByThinkPage($where = [], $order = [], $listRow = 15){
        return $this->where($where)->where('deleted',self::$DELETED_FALSE)->order($order)->paginate($listRow);
    }

    /**
     * 查询已删除的数据
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function selectDeletedAll(){
        return $this->where('deleted',self::$DELETED_TRUE)->select();
    }

    /**
     * @param $id
     * @param string|array $fields
     * @param string|array $field_values
     * @return int
     */
    public function updateField($id,$fields,$field_values){
        return $this->where('id',$id)->setField($fields,$field_values);
    }
}