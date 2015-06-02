<?php
namespace Devbot\Plugin\Drupal\Install\Plugin;

use Devbot\Plugin\Drupal\VersionDetector as DrupalVersionDetector;
use Devbot\Install\Plugin\AbstractPlugin;
use Devbot\Install\Plugin\PluginEnvironment;

class Settings extends AbstractPlugin
{
    const DEFAULT_SETTINGS_FILE = 'public/sites/default/settings.php';
    const DEFAULT_SETTINGS_GLOBAL_TEMPLATE = 
        'public/sites/default/default.settings.php';
    const DEFAULT_SETTINGS_SITE_TEMPLATE = 
        'public/sites/default/template.settings.php';
    
    public function getPluginId()
    {
        return 'drupal-settings';
    }
    
    public function install(PluginEnvironment $env)
    {
        if (!$this->isSupportedDrupalVersion($env)) {
            return;
        }
        
        $fs = $env->getSiteFilesystem();
        if ($fs->has(self::DEFAULT_SETTINGS_FILE)) {
            return;
        }
        $paths = [
            self::DEFAULT_SETTINGS_SITE_TEMPLATE, 
            self::DEFAULT_SETTINGS_GLOBAL_TEMPLATE
        ];
        
        $templatePath = null;
        foreach ($paths as $path) {
            if ($fs->has($path)) {
                $templatePath = $path;
                break;
            }
        }
        if ($templatePath === null) {
            $this->logger->warning('No settings.php template found');
            return;
        }
        
        $this->logger->info('Creating settings file from template');
        $fs->copy($templatePath, self::DEFAULT_SETTINGS_FILE);
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