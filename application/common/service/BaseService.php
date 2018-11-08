<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/15
 * Time: 16:19
 */

namespace app\common\service;


abstract class BaseService
{

    /**
     * 软删除
     * @param $id
     * @return int
     */
    public abstract function delete($id);

    /**
     * 强制删除
     * @param $id
     * @return int
     */
    public abstract function deleteForce($id);

    /**
     * @param $id
     * @return $this|array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public abstract function getById($id);

    /**
     * @param $id
     * @param $field_values
     * @return false|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public abstract function baseSave($id, $field_values);

    /**
     * thinkphp分页查询
     * @param array $where
     * @param array $order
     * @param int $listRow
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public abstract function baseList($where = [],$order = [],$listRow = 15);

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
    public abstract function baseListByPage($page = 1, $listRow = 15, $where = [], $order = []);


}