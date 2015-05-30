<?php
namespace Devbot\Install\Plugin;

use Psr\Log\LoggerAwareTrait;

abstract class AbstractPlugin implements PluginInterface
{
    use LoggerAwareTrait;
    
    function install(PluginEnvironment $env)
    {
        
    }
    
    function archive(PluginEnvironment $env)
    {
        
    }
}