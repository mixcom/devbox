<?php
namespace Devbot\Plugins\Site\Task;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use Devbot\Core\Task\Task;

class GitCloneTask extends Task
{
    protected $source;
    protected $target;
    
    public function autoTarget($source, $dir)
    {
        $basename = basename($source, '.git');
        return $dir . DIRECTORY_SEPARATOR . $basename;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }
    
    protected function validate()
    {
        if (!isset ($this->source)) {
            throw new \LogicException('No source set');
        }
        if (!isset ($this->target)) {
            throw new \LogicException('No target set');
        }
    }
    
    public function run()
    {
        $this->validate();
        
        if (file_exists($this->target)) {
            $this->logger->warning('Directory {target} already exists, doing nothing', [
                'target' => $this->target,
            ]);
            return;
        }
        
        $this->logger->info('Cloning {source} into {target}', [
            'source' => $this->source,
            'target' => $this->target,
        ]);
        
        $process = $this->buildProcess();
        
        $logger = $this->logger;
        $process->run(function ($type, $buffer) use ($logger) {
            $logger->info($buffer);
        });
        
        $exitCode = $process->getExitCode();
        if ($exitCode != 0) {
            $this->logger->error('Error from git:');
            $this->logger->error($process->getErrorOutput());
            
            return TaskInterface::TASK_FAILED;
            
        } else {
          $this->logger->info('Done cloning {source} into {target}', [
                'source' => $this->source,
                'target' => $this->target,
            ]);
            
            return TaskInterface::TASK_OK;
            
        }
    }
    
    protected function buildProcess() {
        $args = ['git', 'clone', $this->source, $this->target];
        $builder = new ProcessBuilder($args);
        $process = $builder->getProcess();
        return $process;
    }
}