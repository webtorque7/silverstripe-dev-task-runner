<?php

namespace Webtorque\DevTaskRunner;

use SilverStripe\CronTask\Interfaces\CronTask;
use SilverStripe\Core\Config\Config;
use Webtorque\DevTaskRunner\Model\DevTaskRun;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\FieldType\DBDatetime;

class DevTaskRunnerCronTask implements CronTask
{
    private static $schedule = '*/2 * * * *';

    public function getSchedule()
    {
        return Config::inst()->get('DevTaskRunnerCronTask', 'schedule');
    }

    public function process()
    {
        $nextTask = DevTaskRun::get_next_task();
        if ($nextTask) {
            //create task instance
            $task = Injector::inst()->create($nextTask->Task);

            //get params
            $params = explode(' ', $nextTask->Params);
            $paramList = array();
            if ($params) {
                foreach ($params as $param) {
                    $parts = explode('=', $param);
                    if (count($parts) === 2) {
                        $paramList[$parts[0]] = $parts[1];
                    }
                }
            }

            //set starting flag
            $nextTask->Status = 'Running';
            $nextTask->write();
            echo 'Starting task ' . $task->getTitle() . "\n";

            //execute task
            $request = new HTTPRequest('GET', 'dev/tasks/' . $nextTask->Task, $paramList);
            $task->run($request);

            //set finished flag
            $nextTask->Status = 'Finished';
            $nextTask->FinishDate = DBDatetime::now()->getValue();
            $nextTask->write();
            echo 'Finished task ' . $task->getTitle() . "\n";
        }
    }
}