<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/10/29
 * Time: 16:53
 */

namespace app\job\controller;


use think\Queue;

class Job
{
    public function test(){
        $jobHandler = config('job_handler.test');
        $jobQueueName = 'test';
        $jobData = [ 'ts' => time(), 'bizId' => uniqid() , 'a' => 1 ] ;
        $isPushed = Queue::push($jobHandler,$jobData,$jobQueueName);
        if( $isPushed !== false ){
            echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
        }else{
            echo 'Oops, something went wrong.';
        }
    }
}