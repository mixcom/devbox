<?php
namespace Devbot\Plugin\Drupal\Install\Plugin;

use Devbot\Install\Filesystem\Helper as FilesystemHelper;
use Devbot\Install\Plugin\AbstractPlugin;
use Devbot\Install\Plugin\PluginEnvironment;

class Files extends AbstractPlugin
{
    const DEFAULT_FILES_DIRECTORY = 'public/sites/default/files';
    
    public function archive(PluginEnvironment $env)
    {
        $fs = $env->getMountManager();
        $fsHelper = new FilesystemHelper($fs);
        
        $prefix = self::DEFAULT_FILES_DIRECTORY;
        $source = PluginEnvironment::PREFIX_SITE . '://' . $prefix;
        $target = PluginEnvironment::PREFIX_DUMP . '://';
        
        if ($fs->has($source)) {
            $fsHelper->copyDirectory($source, $target);
        }
    }
    
    public function install(PluginEnvironment $env)
    {
        $fs = $env->getMountManager();
        $fsHelper = new FilesystemHelper($fs);
        
        $prefix = self::DEFAULT_FILES_DIRECTORY;
        $source = PluginEnvironment::PREFIX_DUMP . '://';
        $target = PluginEnvironment::PREFIX_SITE . '://' . $prefix;
        
        $fsHelper->copyDirectory($source, $target);
    }
}