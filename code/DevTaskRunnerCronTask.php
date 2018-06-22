<?php

namespace Webtorque\DevTaskRunner;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\CronTask\Interfaces\CronTask;
use SilverStripe\ORM\FieldType\DBDatetime;
use Webtorque\DevTaskRunner\Models\DevTaskRun;

/**
 * Created by PhpStorm.
 * User: Conrad
 * Date: 22/01/2016
 * Time: 9:58 AM
 */
class DevTaskRunnerCronTask implements CronTask
{
	private static $schedule = '*/2 * * * *';

	public function getSchedule() {
		return Config::inst()->get('DevTaskRunnerCronTask', 'schedule');
	}

	public function process() {
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

			echo 'Starting task ' . $task->getTitle() . "\n";
			//remove so it doesn't get rerun
			$nextTask->Status = 'Running';
			$nextTask->write();

			$request = new HTTPRequest('GET', 'dev/tasks/' . $nextTask->Task, $paramList);
			$task->run($request);

			$nextTask->Status = 'Finished';
			$nextTask->FinishDate = DBDatetime::now()->getValue();
			$nextTask->write();

			echo 'Finished task ' . $task->getTitle() . "\n";
		}
	}
}