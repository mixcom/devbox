<?php
namespace Devbot\Install\Plugin;

use Psr\Log\LoggerAwareInterface;

interface PluginInterface extends LoggerAwareInterface
{
    function getPluginId();
    
    function install(PluginEnvironment $env);
    
    function archive(PluginEnvironment $env);
}
