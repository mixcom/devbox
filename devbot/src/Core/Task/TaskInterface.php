<?php
namespace Devbot\Core\Task;

interface TaskInterface
{
    const TASK_OK = 'taskOK';
    const TASK_FAILED = 'taskFailed';
    
    function run();
}
