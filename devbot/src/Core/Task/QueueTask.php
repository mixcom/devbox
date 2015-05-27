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
    
    function __construct(array $tasks, LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        foreach ($tasks as $task) {
            if (!$task instanceof TaskInterface) {
                throw new \InvalidArgumentException("All tasks should implement TaskInterface");
            }
        }
        $this->tasks = $tasks;
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