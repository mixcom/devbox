<?php
namespace Devbot\Site\Install\Plugin;

use Devbot\Install\Plugin\PluginEnvironment;
use Devbot\Install\Plugin\AbstractPlugin;

use Symfony\Component\Process\ProcessBuilder;


class PublicSymlink extends AbstractPlugin
{
    protected $publicDirCandidates = [
        'src',
        'www',
        'html',
    ];
    
    public function setPublicDirCandidates(array $candidates)
    {
        $this->publicDirCandidates = $candidates;
        return $this;
    }
    
    public function getPluginId()
    {
        return 'public-symlink';
    }
    
    public function install(PluginEnvironment $env)
    {
        $this->createPublicSymlink(
            $env->getProcessBuilder(),
            $env->getSiteDirectory()
        );
    }
    
    protected function createPublicSymlink(ProcessBuilder $processBuilder, $siteDir)
    {
        $publicDir = $siteDir . DIRECTORY_SEPARATOR . 'public';
        
        if (file_exists($publicDir)) {
            return;
        }
        
        $wwwDir = $siteDir;
        
        foreach ($this->publicDirCandidates as $candidateName) {
            $publicDirCandidate = $siteDir . DIRECTORY_SEPARATOR . $candidateName;
            if (file_exists($publicDirCandidate)) {
                $wwwDir = $publicDirCandidate;
                break;
            }
        }
        
        $processBuilder->setArguments(['ln', '-s', $wwwDir, $publicDir]);
        $process = $processBuilder->getProcess();
        
        $this->logger->info(
            'Creating symlink {link} to {target}', 
            [
                'link' => $publicDir,
                'target' => $wwwDir,
            ]
        );
        $process->run();
    }
}
