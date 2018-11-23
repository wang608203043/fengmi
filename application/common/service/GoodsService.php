<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/6
 * Time: 15:48
 */

namespace app\common\service;


use app\common\component\CodeResponse;
use app\common\model\BaseModel;
use app\common\model\Goods;
use think\Cache;

class GoodsService extends BaseService
{

    protected $model;
    public function __construct()
    {
        $this->model = new Goods();
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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getActiveBanner(){
        $data = [];
        $list = $this->model->where(['deleted'=>BaseModel::$DELETED_FALSE])->order('sort')->select();
        if ($list){
            foreach ($list as $key=>$item) {
                $data[$key] = [
                    'id'=>$item->id,
                    'cname'=>$item->cname,
                    'img_url'=>$item->img_url
                ];
            }
        }
        return $data;
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
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByRecommend($openid)
    {
        $list = $this->model->where(['deleted'=>BaseModel::$DELETED_FALSE,'is_down'=>BaseModel::$ON_LINE,'is_recommend'=>1])
            ->field('id,gname,img_url,price')->select();
        $cache = Cache::get('collection_goods_'.$openid);
        foreach ($list as &$item) {
            $item['collected'] = 0;
            if ($cache){
                if (in_array($item->id,$cache)){
                    $item['collected'] = 1;
                }
            }
        }
        return $list;
    }

    /**
     * @param $openid
     * @param $page
     * @param int $listRow
     * @param array $where
     * @param array $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getByCategory($openid,$page, $listRow = 15, $where = [], $order = [])
    {
        $list = $this->model->where(['deleted'=>BaseModel::$DELETED_FALSE,'is_down'=>BaseModel::$ON_LINE])
            ->where($where)->field('id,gname,img_url,price')->page($page,$listRow)
            ->order($order)->select();
        $cache = Cache::get('collection_goods_'.$openid);
        foreach ($list as &$item) {
            $item['collected'] = 0;
            if ($cache){
                if (in_array($item->id,$cache)){
                    $item['collected'] = 1;
                }
            }
        }
        return $list;
    }

    /**
     * @param $id
     * @param $openid
     * @return Goods|array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetailAndComment($id,$openid)
    {
        $goods = $this->model->findById($id);
        $comments = $goods->goodsComment()->where(['deleted'=>BaseModel::$DELETED_FALSE])->order('create_time desc')->limit(10)->select();
        $list = [];
        $goods['collected'] = 0;
        if ($cache = Cache::get('collection_goods_'.$openid)){
            if (in_array($id,$cache)){
                $goods['collected'] = 1;
            }
        }
        foreach ($comments as $comment) {
            $list[] = [
                'nick_name'=>$comment->user->nick_name,
                'img_url'=>$comment->user->img_url,
                'content'=>$comment->content,
                'create_time'=>$comment->create_time
            ];
        }
        $goods['goodsComment'] = $list;
        return $goods;
    }

    /**
     * @param $goods_id
     * @param $page
     * @param $listRow
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function moreComment($goods_id, $page, $listRow)
    {
        $goods = $this->model->findById($goods_id);
        $comments = $goods->goodsComment()->where(['deleted'=>BaseModel::$DELETED_FALSE])->order('create_time desc')->page($page,$listRow)->select();
        $list = [];
        foreach ($comments as $comment) {
            $list[] = [
                'nick_name'=>$comment->user->nick_name,
                'img_url'=>$comment->user->img_url,
                'content'=>$comment->content,
                'create_time'=>$comment->create_time
            ];
        }
        return $list;
    }

    /**
     * @param $goods_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStocks($goods_id)
    {
        $goods = $this->model->findById($goods_id);
        $list = $goods->stock()->where(['deleted'=>BaseModel::$DELETED_FALSE])->select();
        return $list;
    }

    /**
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategory()
    {
        return $this->model->category()->select();
    }

    /**
     * @param $ids
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsByIds($ids)
    {
        if (is_array($ids)){
            $list = $this->model->whereIn('id',$ids)
                ->where(['deleted'=>BaseModel::$DELETED_FALSE,'is_down'=>BaseModel::$ON_LINE,'is_recommend'=>1])
                ->field('id,gname,img_url,price')->select();
            foreach ($list as &$item) {
                $item['collected'] = 1;
            }
        }
        CodeResponse::error(CodeResponse::CODE_SYSTEM_ERROR,null,'网络异常');
    }
}