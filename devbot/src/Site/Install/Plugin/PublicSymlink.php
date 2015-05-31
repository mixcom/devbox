<?php
namespace Devbot\Site\Install\Plugin;

use Devbot\Install\Plugin\PluginEnvironment;
use Devbot\Install\Plugin\AbstractPlugin;

use Symfony\Component\Process\ProcessBuilder;


class PublicSymlink extends AbstractPlugin
{
    public function getPluginId()
    {
        return 'public-symlink';
    }
    
    public function install(PluginEnvironment $env)
    {
        $this->createPublicSymlink($env->getSiteDirectory());
    }
    
    protected function createPublicSymlink($siteDir)
    {
        $publicDir = $siteDir . DIRECTORY_SEPARATOR . 'public';
        
        if (file_exists($publicDir)) {
            return;
        }
        
        $processBuilder = new ProcessBuilder(['ln', '-s', $siteDir, $publicDir]);
        $process = $processBuilder->getProcess();
        
        $this->logger->info('Creating symlink {link}', ['link' => $publicDir]);
        $process->run();
    }
}
