<?php
namespace Devbot\Plugin\Drupal\Install\Plugin;

use Devbot\Plugin\Drupal\VersionDetector as DrupalVersionDetector;
use Devbot\Install\Filesystem\Helper as FilesystemHelper;
use Devbot\Install\Plugin\AbstractPlugin;
use Devbot\Install\Plugin\PluginEnvironment;

use Symfony\Component\Process\ProcessBuilder;


class Files extends AbstractPlugin
{
    const DEFAULT_FILES_DIRECTORY = 'public/sites/default/files';
    
    public function getPluginId()
    {
        return 'drupal-files';
    }
    
    public function archive(PluginEnvironment $env)
    {
        if (!$this->isSupportedDrupalVersion($env)) {
            return;
        }
        
        $fs = $env->getMountManager();
        $fsHelper = new FilesystemHelper($fs);
        
        $prefix = self::DEFAULT_FILES_DIRECTORY;
        $source = PluginEnvironment::PREFIX_SITE . '://' . $prefix;
        $target = PluginEnvironment::PREFIX_DUMP . '://';
        
        if ($fs->has($source)) {
            $this->logger->info('Archiving Drupal user files');
            $fsHelper->copyDirectory($source, $target);
        }
    }
    
    public function install(PluginEnvironment $env)
    {
        if (!$this->isSupportedDrupalVersion($env)) {
            return;
        }
        
        $this->copyFiles($env);
        $this->prepareFilesPermissions($env);
    }
    
    public function copyFiles(PluginEnvironment $env)
    {
        $fs = $env->getMountManager();
        $fsHelper = new FilesystemHelper($fs);
        
        $prefix = self::DEFAULT_FILES_DIRECTORY;
        $source = PluginEnvironment::PREFIX_DUMP . '://';
        $target = PluginEnvironment::PREFIX_SITE . '://' . $prefix;
        
        $this->logger->info('Copying Drupal user files');
        
        $fsHelper->copyDirectory($source, $target);
    }
    
    public function prepareFilesPermissions(PluginEnvironment $env)
    {
        $filesDirectory = $env->getSiteDirectory()
            . DIRECTORY_SEPARATOR
            . self::DEFAULT_FILES_DIRECTORY;
        
        $processBuilder = $env->getProcessBuilder();
        $processBuilder->setArguments(['chmod', '-R', '777', $filesDirectory]);
        $process = $processBuilder->getProcess();
        
        $this->logger->info('Setting permissions on Drupal files');
        $process->run();
    }
    
    protected function isSupportedDrupalVersion(PluginEnvironment $env)
    {
        $versionDetector = new DrupalVersionDetector();
        $version = $versionDetector->detectMajorVersion($env->getSiteFilesystem());
        
        switch ($version) {
            case DrupalVersionDetector::VERSION_6:
            case DrupalVersionDetector::VERSION_7:
                return true;
            case DrupalVersionDetector::NOT_DRUPAL:
                return false;
            default:
                $this->logger->warning(
                    'Unsupported Drupal version: {version}',
                    [
                        'version' => $version,
                    ]
                );
                return false;
        }
    }
}