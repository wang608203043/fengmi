<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/23
 * Time: 12:35
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\ReceiveService;
use think\Request;

class Receive extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new ReceiveService();
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $status = input('status',100);
        $where = [];
        if ($status != 100){
            $where['used'] = $status;
        }
        $list = $this->service->baseList($where,['create_time'=>'desc']);
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function del(){
        $id = input('id');
        $res = $this->service->delete($id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }
}