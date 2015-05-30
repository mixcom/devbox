<?php
namespace Devbot\Plugins\Site\VcsClone;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use Psr\Log\LoggerAwareTrait;

class GitCloner implements ClonerInterface
{
    use LoggerAwareTrait;
    
    protected $source;
    protected $target;
    protected $branch;
    
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }
    
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }
    
    public function setBranch($branch)
    {
        $this->branch = $branch;
        return $this;
    }
    
    public function deriveTargetFromSourceInDirectory($dir)
    {
        $basename = basename($this->source, '.git');
        $this->target = $dir . DIRECTORY_SEPARATOR . $basename;
        return $this->target;
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
    
    public function runClone()
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
            
            throw new \RuntimeException(
                'Error from git: ' . $process->getErrorOutput()
            );
        }
        
        $this->logger->info('Done cloning {source} into {target}', [
            'source' => $this->source,
            'target' => $this->target,
        ]);
    }
    
    protected function buildProcess() {
        $args = ['git', 'clone'];
        if ($this->branch !== null) {
            $args[] = '-b';
            $args[] = $this->branch;
        }
        $args[] = $this->source;
        $args[] = $this->target;
        
        $builder = new ProcessBuilder($args);
        $process = $builder->getProcess();
        return $process;
    }
}