<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/11/7
 * Time: 10:30
 */

namespace app\admin\controller;


use app\common\component\CodeResponse;
use app\common\service\GoodsStockService;
use think\Request;

class GoodsStock extends BaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->service = new GoodsStockService();
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $where = ['goods_id'=>input('id')];
        $goods = $this->service->getGoods(input('id'));
        $list = $this->service->baseList($where);
        $this->assign('list',$list);
        $this->assign('goods',$goods);
        return $this->fetch();
    }

    /**
     * @return mixed
     */
    public function add(){
        $goods_id = input('goods_id');
        $this->assign('goods_id',$goods_id);
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
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save(){
        $id = input('id');
        $data['goods_id'] = input('goods_id');
        $data['stock_name'] = input('stock_name');
        $data['attribute'] = input('attribute');
        $data['stock'] = input('stock');
        $data['price'] = input('price');
        $data['img_url'] = input('img_url');
        $res = $this->service->baseSave($id, $data);
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
}