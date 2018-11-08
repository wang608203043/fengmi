<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/19
 * Time: 16:27
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\GoodsService;
use think\Image;
use think\Request;

class Goods extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new GoodsService();
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $keywords = input('keywords');
        $where = [];
        if ($keywords){
            $where = ['gname'=>['like','%'.$keywords.'%']];
        }
        $list = $this->service->baseList($where,['create_time'=>'desc']);
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
        $category = $this->service->getCategory();
        $this->assign('category',$category);
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
        $category = $this->service->getCategory();
        $this->assign('category',$category);
        $this->assign('model',$model);
        return $this->fetch();
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(){
        $id = input('id');
        $data['category_id'] = input('category_id');
        $data['gname'] = input('gname');
        $data['price'] = input('price');
        $data['postage'] = input('postage');
        $data['img_url'] = input('img_url');
        $data['images'] = trim(input('images'),',');
        $data['content'] = trim(input('content'),',');
        $res = $this->service->baseSave($id, $data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
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

    public function del(){
        $id = input('id');
        $res = $this->service->delete($id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    /**
     * @return \think\response\Json
     */
    public function recommend(){
        $id = input('id');
        $recommend = input('is_recommend');
        if ($recommend){
            $recommend = 0;
        }else{
            $recommend = 1;
        }
        $res = $this->service->change($id,'is_recommend',$recommend);
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

}