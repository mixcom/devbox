<?php
namespace Devbot\Plugins\Site\VcsClone;

use Psr\Log\LoggerAwareInterface;

interface ClonerInterface extends LoggerAwareInterface
{
    function setSource($source);
    
    function setTarget($target);
    
    function setBranch($branch);
    
    function deriveTargetFromSourceInDirectory($directory);
    
    function runClone();
}