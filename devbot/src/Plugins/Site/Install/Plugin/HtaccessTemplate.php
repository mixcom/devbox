<?php
namespace Devbot\Plugins\Site\Install\Plugin;

use Devbot\Install\Plugin\PluginEnvironment;
use Devbot\Install\Plugin\AbstractPlugin;

use League\Flysystem\Filesystem;

class HtaccessTemplate extends AbstractPlugin
{
    const SOURCE_FILE = 'public/.htaccess.tpl';
    const TARGET_FILE = 'public/.htaccess';
    
    public function install(PluginEnvironment $env)
    {
        $this->copyHtaccessTemplate($env->getSiteFilesystem());
    }
    
    protected function copyHtaccessTemplate(Filesystem $siteFilesystem)
    {
        $source = self::SOURCE_FILE;
        $target = self::TARGET_FILE;
        
        if ($siteFilesystem->has($target)) {
            return;
        }
        if (!$siteFilesystem->has($source)) {
            return;
        }
        
        $this->logger->info(
            'Copying {source} => {target}', 
            [
                'source' => $source,
                'target' => $target,
            ]
        );
        
        $siteFilesystem->copy($source, $target);
    }
}
