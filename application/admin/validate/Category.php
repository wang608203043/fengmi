<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/18
 * Time: 14:34
 */

namespace app\admin\validate;


use think\Validate;

class Category extends Validate
{
    protected $rule = [
        'img_url'=>'require',
        'cname'=>'require',
    ];

    protected $message = [
        'img_url.require'=>'请上传图片',
        'cname.require'=>'名称必须填写',
    ];

    protected $scene = [

    ];
}