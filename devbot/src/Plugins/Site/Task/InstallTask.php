<?php
namespace Devbot\Plugins\Site\Task;

use Devbot\Core\Task\Task;

class InstallTask extends Task
{
    protected $directory;
    protected $force = false;
    
    public function getDirectory()
    {
        return $this->directory;
    }
    
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
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
    
    protected function validate()
    {
        if (!isset ($this->directory)) {
            throw new \LogicException('No directory set');
        }
    }
    
    public function run()
    {
        $this->logger->error('Not implemented, running for directory {directory}', [
            'directory' => $this->directory,
        ]);
    }
}