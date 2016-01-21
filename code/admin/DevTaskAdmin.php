<?php


class DevTaskAdmin extends ModelAdmin
{
	private static $managed_models = array('DevTaskRun');

	private static $menu_title = 'Dev Tasks';
	private static $url_segment = 'dev-tasks';

	public function getEditForm($id = null, $fields = null)
	{
		$form = parent::getEditForm($id, $fields);

		$task = new DevTaskRunnerCronTask();
		$cron = Cron\CronExpression::factory($task->getSchedule());
		$nextRun = $cron->getNextRunDate()->format('Y-m-d H:i:s');
		$form->Fields()->unshift(LiteralField::create('NextRunMessage', '<p class="message">Next run at ' . $nextRun . '</p>'));

		return $form;
	}
}