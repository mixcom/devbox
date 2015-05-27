<?php
namespace Devbot\Plugins\Site\Task;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;
use Psr\Log\LoggerInterface;
use Devbot\Core\Task\Task;

class InstallTask extends Task
{
    protected $directory;
    protected $force = false;
    
    public function getDirectory()
    {
        return $this->directory;
    }
    
    public function getForce()
    {
        return $this->force;
    }
    
    public function setForce($force)
    {
        $this->force = $force;
        return $this;
    }
    
    public function __construct($directory, LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->directory = $directory;
    }
    
    public function run()
    {
        $this->logger->error('Not implemented, running for directory {directory}', [
            'directory' => $this->directory,
        ]);
    }
}