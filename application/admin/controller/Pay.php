<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/24
 * Time: 14:53
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\PayService;
use think\Request;

class Pay extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new PayService();
    }
    /**
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $keywords = input('keywords');
        $where = [];
        if ($keywords){
            $where['serial'] = ['like','%'.$keywords.'%'];
        }
        $list = $this->service->baseList($where,['create_time'=>'desc']);
        $this->assign('list', $list);
        return $this->fetch();
    }


    public function del(){
        $id = input('id');
        $res = $this->service->delete($id);
        return $res ? CodeResponse::format($res) : CodeResponse::fail();
    }
}