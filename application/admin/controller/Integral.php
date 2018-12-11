<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/14
 * Time: 17:31
 */

namespace app\admin\controller;


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
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = $this->service->baseList();
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(){
        $coupons = $this->service->getAllCoupon();
        $this->assign('coupons',$coupons);
        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit(){
        $id = input('id');
        $model = $this->service->getById($id);
        $coupons = $this->service->getAllCoupon();
        $this->assign('coupons',$coupons);
        $this->assign('model',$model);
        return $this->fetch();
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(){
        $result = $this->validate(input('post.'),'Integral');
        if ($result !== true){
            CodeResponse::error(CodeResponse::CODE_PARAMS_ERROR,['error'=>$result]);
        }
        $id = input('id');
        $data['coupon_id'] = input('coupon_id',0);
        $data['gname'] = input('gname','');
        $data['price'] = input('price',0);
        $data['img_url'] = input('img_url','');
        $data['stock'] = input('stock',0);
        $data['genre'] = input('genre',1);
        $res = $this->service->baseSave($id,$data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    public function del(){
        $id = input('id');
        $res = $this->service->delete($id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * 文件上传
     * 参数  filename  文件名  fileType 文件类型（base64,file）  file
     */
    public function upload(){
        $fileType = input('fileType');
        $filename = input('filename');
        $file_path = '';
        if ($fileType == 'base64'){
            $file_path = base64_image_content(input($filename),UPLOAD_PATH);
        }elseif ($fileType == 'file'){
            $file = $this->request->file($filename);
            $info = $file->move(UPLOAD_PATH);
            $file_path = '/upload/'.$info->getSaveName();
        }elseif ($file = $this->request->file('file')){
            $info = $file->move(UPLOAD_PATH);
            if ($info){
                $file_path = '/upload/'.$info->getSaveName();
            }else{
                return CodeResponse::fail(CodeResponse::CODE_ERROR_UPLOAD);
            }
        }
        return CodeResponse::format(['upload_url'=>$file_path]);
    }

    /**
     * @return \think\response\Json
     */
    public function changeIsDown(){
        $id = input('id');
        $is_down = input('is_down');
        if ($is_down){
            $is_down = 0;
        }else{
            $is_down = 1;
        }
        $res = $this->service->change($id,'is_down',$is_down);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }
}