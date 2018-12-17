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

class Integral extends Validate
{
    protected $rule = [
        'coupon_id'=>'require',
        'gname'=>'require',
        'price'=>'require',
        'img_url'=>'require',
        //'images'=>'require',
        //'content'=>'require',
        'stock'=>'require',
        'genre'=>'require',
    ];

    protected $message = [
        'coupon_id.require'=>'参数异常',
        'gname.require'=>'名称必须填写',
        'price.require'=>'价格必须填写',
        'img_url.require'=>'封面图必须上传',
        //'images.require'=>'轮播图必须上传',
        //'content.require'=>'图文介绍必须上传',
        'stock.require'=>'库存必须填写',
        'genre.require'=>'类型必须填写',
    ];

    protected $scene = [

    ];
}