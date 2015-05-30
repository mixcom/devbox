<?php
namespace Devbot\Install;

use Psr\Log\LoggerAwareTrait;

abstract class AbstractInstaller implements InstallerInterface
{
    use LoggerAwareTrait;
    
    protected $directory;
    protected $archive;
    
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }
    
    public function setArchive($archive)
    {
        $this->archive = $archive;
        return $this;
    }
    
    public function archive()
    {
        
    }
}