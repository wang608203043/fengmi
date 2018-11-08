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
        $list = $this->service->getByRecommend();
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
        $list = $this->service->getByCategory($page, 15, ['category_id'=>$category_id]);
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
        $goods = $this->service->getDetailAndComment($id);
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
}