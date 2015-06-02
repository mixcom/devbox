<?php
namespace Devbot\Site\Install\Plugin;

use Devbot\Install\Plugin\PluginEnvironment;
use Devbot\Install\Plugin\AbstractPlugin;

use Symfony\Component\Process\ProcessBuilder;


class ComposerInstall extends AbstractPlugin
{
    const COMPOSER_FILENAME = 'composer.json';
    
    public function getPluginId()
    {
        return 'composer-install';
    }
    
    public function install(PluginEnvironment $env)
    {
        $this->composerInstall($env->getProcessBuilder(), $env->getSiteDirectory());
    }
    
    protected function composerInstall(ProcessBuilder $processBuilder, $siteDir)
    {
        $composerFile = $siteDir . DIRECTORY_SEPARATOR . self::COMPOSER_FILENAME;
        
        if (!file_exists($composerFile)) {
            return;
        }
        
        $processBuilder->setWorkingDirectory($siteDir);
        $processBuilder->setArguments(['composer', 'install', '--no-interaction']);
        $process = $processBuilder->getProcess();
        
        $this->logger->info(
            'Running composer install on {site}', 
            [
                'site' => $siteDir,
            ]
        );
        $process->run();
    }
}
