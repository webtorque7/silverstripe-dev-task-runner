<?php

namespace Webtorque\DevTaskRunner\Admin;

use SilverStripe\Admin\ModelAdmin;
use Webtorque\DevTaskRunner\Model\DevTaskRun;
use Webtorque\DevTaskRunner\DevTaskRunnerCronTask;
use Cron\CronExpression;
use SilverStripe\Forms\LiteralField;

class DevTaskAdmin extends ModelAdmin
{
    private static $managed_models = array(DevTaskRun::class);
    private static $menu_title = 'Dev Tasks';
    private static $url_segment = 'dev-tasks';

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);
        $task = new DevTaskRunnerCronTask();
        $cron = CronExpression::factory($task->getSchedule());
        $nextRun = $cron->getNextRunDate()->format('Y-m-d H:i:s');
        $form->Fields()->unshift(LiteralField::create('NextRunMessage', '<p class="message">Next run at ' . $nextRun . '</p>'));

        return $form;
    }
}