<?php
namespace Devbot\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DependencyLoaderCompilerPass implements CompilerPassInterface
{
    const APPLICATION_COMPONENT = 'application';
    
    public function process(ContainerBuilder $container)
    {
        $pluginIds = $container->findTaggedServiceIds(
            'extension.plugin'
        );
        
        $plugins = [];
        foreach ($pluginIds as $pluginId => $pluginInfos) {
            foreach ($pluginInfos as $pluginInfo) {
                if (! isset ($pluginInfo['type'])) {
                    continue;
                }
                $type = $pluginInfo['type'];
                $plugins[$type][] = $pluginId;
            }
        }
        
        $socketIds = $container->findTaggedServiceIds(
            'extension.socket'
        );
        
        foreach ($socketIds as $socketId => $socketInfos) {
            $socketDefinition = $container->findDefinition($socketId);
            
            foreach ($socketInfos as $socketInfo) {
                if (! isset ($socketInfo['type'])) {
                    continue;
                }
                $type = $socketInfo['type'];
                if (isset ($socketInfo['method'])) {
                    $method = 'add';
                } else {
                    $method = 'addPlugin';
                }
                
                if (isset ($plugins[$type])) {
                    foreach ($plugins[$type] as $pluginId) {
                        $socketDefinition->addMethodCall(
                            $method,
                            [new Reference($pluginId)]
                        );
                    }
                }
            }
        }
    }
}
