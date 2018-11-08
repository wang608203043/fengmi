<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/18
 * Time: 17:27
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\CarouselService;
use app\common\service\CategoryService;
use think\Request;

class Category extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new CategoryService();
    }
    /**
     * 获取分类
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBanner(){
        $list = $this->service->getActiveBanner();
        return CodeResponse::format($list);
    }

}