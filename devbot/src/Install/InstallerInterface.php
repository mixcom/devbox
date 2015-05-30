<?php
namespace Devbot\Install;

use Psr\Log\LoggerAwareInterface;

interface InstallerInterface extends LoggerAwareInterface
{
    function setDirectory($directory);
    
    function setArchive($archive);
    
    function install();
    
    function archive();
}