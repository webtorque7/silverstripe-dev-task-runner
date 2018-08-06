<?php

namespace Webtorque\DevTaskRunner\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;

class DevTaskRun extends DataObject
{
    private static $table_name = 'DevTaskRun';

    private static $db = array(
        'Task' => 'Varchar(150)',
        'Params' => 'Varchar(255)',
        'Status' => 'Enum("Queued,Running,Finished", "Queued")',
        'FinishDate' => 'Datetime'
    );

    private static $summary_fields = array(
        'TaskTitle' => 'Task',
        'Params' => 'Params',
        'Status' => 'Status',
        'FinishDate' => 'Finish Date'
    );

    private static $default_sort = 'Created DESC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $taskList = array();

        //defined allowed task list
        $tasks = $this->config()->task_list;

        //default to all tasks
        if (!$tasks) {
            $tasks = ClassInfo::subclassesFor(BuildTask::class);
            array_shift($tasks);
        }

        foreach ($tasks as $task) {
            $taskList[$task] = singleton($task)->getTitle();
        }

        $fields->addFieldsToTab('Root.Main', array(
            DropdownField::create('Task', 'Task', $taskList),
            TextField::create('Params')->setDescription('add a list of params to be passed to task, separate with space, e.g. param1=value1 param2=value2')
        ));

        return $fields;
    }

    public function TaskTitle()
    {
        if ($this->Task) {
            return singleton($this->Task)->getTitle();
        }

        return '';
    }

    public static function get_next_task()
    {
        return DevTaskRun::get()->filter('Status', 'Queued')->sort('Created ASC')->first();
    }
}