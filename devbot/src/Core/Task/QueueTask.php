<?php
namespace Devbot\Core\Task;

use Psr\Log\LoggerInterface;

class QueueTask extends Task
{
    protected $tasks;
    protected $breakOnFailure = true;
    
    function getBreakOnFailure()
    {
        return $this->breakOnFailure;
    }
    
    function setBreakOnFailure($break)
    {
        $this->breakOnFailure = $break;
        return $this;
    }
    
    public function getTasks()
    {
        return $this->tasks;
    }
    
    public function addTask(TaskInterface $task)
    {
        $this->tasks[] = $task;
        return $this;
    }
    
    public function setTasks(array $tasks)
    {
        $this->tasks = [];
        foreach ($tasks as $task) {
            $this->addTask($task);
        }
        return $this;
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->setTasks([]);
    }
    
    public function run()
    {
        foreach ($this->tasks as $i => $task) {
            $this->logger->debug('Running task {task}', ['task' => $i]);
            $result = $task->run();
            if ($this->breakOnFailure && $result === TaskInterface::TASK_FAILED) {
                $this->logger->notice('Task {task} failed, stopping', ['task' => $i]);
                break;
            }
        }
    }
}