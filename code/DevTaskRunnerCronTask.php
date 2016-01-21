<?php

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
						$param[$parts[0]] = $parts[1];
					}
				}
			}

			echo 'Starting task ' . $task->getTitle() . "\n";
			//remove so it doesn't get rerun
			$nextTask->Status = 'Running';
			$nextTask->write();

			$request = new SS_HTTPRequest('GET', 'dev/tasks/' . $nextTask->Task, $paramList);
			$task->run($request);

			$nextTask->Status = 'Finished';
			$nextTask->FinishDate = SS_Datetime::now()->getValue();
			$nextTask->write();

			echo 'Finished task ' . $task->getTitle() . "\n";
		}
	}
}