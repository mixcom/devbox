<?php
namespace Devbot\Plugins\Site\Task;

use Devbot\Core\Task\Task;
use Devbot\Core\Task\QueueTask;

class SetupTask extends Task
{
    protected $gitCloneTask;
    protected $installTask;
    
    protected $innerQueueTask;
    
    public function __construct()
    {
        parent::__construct();
        $this->innerQueueTask = new QueueTask();
    }
    
    public function getGitCloneTask()
    {
        return $this->gitCloneTask;
    }
    
    public function setGitCloneTask(GitCloneTask $task)
    {
        $this->gitCloneTask = $task;
        return $this;
    }
    
    public function getInstallTask()
    {
        return $this->installTask;
    }
    
    public function setInstallTask(InstallTask $task)
    {
        $this->installTask = $task;
        return $this;
    }
    
    protected function updateQueueTask()
    {
        $tasks = array_filter([$this->gitCloneTask, $this->installTask]);
        
        // share logger between tasks
        $logger = $this->getLogger();
        foreach ($tasks as $task) {
            $task->setLogger($logger);
        }
        
        $this->innerQueueTask->setTasks($tasks);
    }
    
    public function run()
    {
        $this->updateQueueTask();
        return $this->innerQueueTask->run();
    }
}