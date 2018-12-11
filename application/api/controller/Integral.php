<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/12/11
 * Time: 14:50
 */

namespace app\api\controller;


use app\common\component\CodeResponse;
use app\common\service\IntegralService;
use think\Request;

class Integral extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new IntegralService();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        $page = input('page',1);
        $list = $this->service->baseListByPage($page,15,[],['genre'=>'asc']);
        return CodeResponse::format($list);
    }
}