<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/18
 * Time: 18:19
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\GoodsService;
use think\Cache;
use think\Request;

class Goods extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new GoodsService();
    }
    /**
     * 获取推荐商品
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRecommendList(){
        $openid = $this->getOpenid();
        $list = $this->service->getByRecommend($openid);
        return CodeResponse::format($list);
    }

    /**
     * 获取分类商品
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCategoryList(){
        $page = input('page',1);
        $category_id = input('category_id');
        $openid = $this->getOpenid();
        $list = $this->service->getByCategory($openid,$page, 15, ['category_id'=>$category_id]);
        return CodeResponse::format($list);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getDetail(){
        $id = input('goods_id');
        $openid = $this->getOpenid();
        $goods = $this->service->getDetailAndComment($id,$openid);
        return CodeResponse::format($goods);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMoreComment(){
        $goods_id = input('goods_id');
        $page = input('page',2);
        $list = $this->service->moreComment($goods_id,$page,10);
        return CodeResponse::format($list);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsStock(){
        $goods_id = input('goods_id');
        $list = $this->service->getStocks($goods_id);
        return CodeResponse::format($list);
    }

    /**
     * 收藏
     * @return \think\response\Json
     */
    public function collect(){
        $goods_id = input('goods_id');
        $openid = $this->getOpenid();
        $opt = input('opt');
        $cache = Cache::get('collection_goods_'.$openid);
        if ($opt != 'cancel'){
            if ($cache){
                array_push($cache,$goods_id);
            }else{
                $cache = [$goods_id];
            }
        }else{
            if ($key = array_search($goods_id,$cache)){
                unset($cache[$key]);
            }else{
                return CodeResponse::fail(CodeResponse::CODE_PARAMS_ERROR,null,'参数错误');
            }
        }
        $res = Cache::set('collection_goods_'.$openid,$cache);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * 获取收藏商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCollection(){
        $openid = $this->getOpenid();
        $cache = Cache::get('collection_goods_'.$openid);
        $list = $this->service->getGoodsByIds($cache);
        return CodeResponse::format($list);
    }

    public function comment(){
        $data['goods_id'] = input('goods_id');
        $data['auth_id'] = $this->getUid();
        $data['content'] = input('content');
        $res = (new GoodsService())->comment($data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }
}