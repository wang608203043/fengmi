<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Author: wang
 * Date: 2018/12/11
 * Time: 11:39
 */

namespace app\api\behavior;


use think\Log;
use think\queue\Job;

class QueueFailedLog
{
    /**
     * @param Job $jobObject
     * @return bool
     */
    public function queueFailed(&$jobObject){
        $failedJobLog = [
            'jobHandlerClassName'   => $jobObject->getName(), // 'application\index\job\Hello'
            'queueName' => $jobObject->getQueue(),			   // 'helloJobQueue'
            'jobData'   => $jobObject->getRawBody()['data'],  // '{'a': 1 }'
            'attempts'  => $jobObject->attempts(),            // 3
        ];
        Log::write($failedJobLog,'queue-failed-log',true);
        return true;
    }
}