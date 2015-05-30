<?php
namespace Devbot\Install\Plugin;

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\Adapter\Local;

class PluginEnvironment
{
    const PREFIX_SITE = 'site';
    const PREFIX_DUMP = 'dump';
    
    protected $siteDirectory;
    protected $dumpDirectory;
    
    public function getSiteDirectory()
    {
        return $this->siteDirectory;
    }
    
    public function getSiteFilesystem()
    {
        return new Filesystem(new Local($this->getSiteDirectory()));
    }
    
    public function getDumpDirectory()
    {
        return $this->dumpDirectory;
    }
    
    public function getDumpFilesystem()
    {
        return new Filesystem(new Local($this->getDumpDirectory()));
    }
    
    public function getMountManager()
    {
        $manager = new MountManager([
            self::PREFIX_SITE => $this->getSiteFilesystem(),
            self::PREFIX_DUMP => $this->getDumpFilesystem(),
        ]);
        return $manager;
    }
    
    public function __construct(
        $siteDirectory, 
        $dumpDirectory
    ) {
        $this->siteDirectory = $siteDirectory;
        $this->dumpDirectory = $dumpDirectory;
    }
}