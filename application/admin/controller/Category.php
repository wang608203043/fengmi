<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/18
 * Time: 14:22
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\CategoryService;
use think\helper\Str;
use think\Request;

class Category extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new CategoryService();
    }

    /**
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = $this->service->baseList();
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function add(){
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
        $this->assign('model',$model);
        return $this->fetch();
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(){
        $result = $this->validate(input('post.'),'Category');
        if ($result !== true){
            CodeResponse::error(CodeResponse::CODE_PARAMS_ERROR,['error'=>$result]);
        }
        $id = input('id');
        //$data['parent_id'] = input('parent_id');
        $data['sort'] = input('sort');
        $data['cname'] = input('cname');
        $data['img_url'] = input('img_url');
        if (Str::contains($data['img_url'],'base64')){
            $data['img_url'] = base64_image_content($data['img_url'],UPLOAD_PATH);
        }
        $res = $this->service->baseSave($id,$data);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

    public function del(){
        $id = input('id');
        $res = $this->service->delete($id);
        return $res ? CodeResponse::format() : CodeResponse::fail();
    }

}