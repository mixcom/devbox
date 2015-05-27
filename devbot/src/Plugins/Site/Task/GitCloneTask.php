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
    
    public static function autoTarget($source, $dir)
    {
        $basename = basename($source, '.git');
        return $dir . DIRECTORY_SEPARATOR . $basename;
    }
    
    public function getSource()
    {
        return $this->source;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function __construct($source, $target, LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->source = $source;
        $this->target = $target;
    }
    
    public function run()
    {
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