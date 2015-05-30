<?php
namespace Devbot\Install\Plugin;

use Psr\Log\LoggerAwareInterface;

interface PluginInterface extends LoggerAwareInterface
{
    function install(PluginEnvironment $env);
    
    function archive(PluginEnvironment $env);
}
