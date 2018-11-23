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

class Coupon extends Validate
{
    protected $rule = [
        'title'=>'require',
        'amount'=>'require',
        'condition'=>'require',
    ];

    protected $message = [
        'condition.require'=>'满减条件必须填写',
        'amount.require'=>'满减金额必须填写',
        'title.require'=>'标题必须填写',
    ];

    protected $scene = [

    ];
}