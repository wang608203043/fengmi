<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/15
 * Time: 16:18
 */

namespace app\admin\controller;


use app\common\service\BaseService;
use think\Controller;
use think\Request;

class BaseController extends Controller
{
    /**
     * @var BaseService
     */
    public $service;
    public static $white_list = [  //白名单
        'admin/Admin/index',
        'admin/Admin/login',
    ];

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->check_auth();
    }

    protected function check_auth()
    {
        $route = $this->request->module() . '/' . $this->request->controller() . '/' . $this->request->dispatch()['module'][2];
        if (!session('admin_user') && !in_array($route, self::$white_list)) {
            $this->redirect('admin/index');
        }
    }

}