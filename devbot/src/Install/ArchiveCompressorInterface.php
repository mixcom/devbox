<?php
namespace Devbot\Install;

use Psr\Log\LoggerAwareInterface;

interface ArchiveCompressorInterface extends LoggerAwareInterface
{
    function setCompressedPath($path);
    
    function setUncompressedDirectory($directory);
    
    function prepareEmptyUncompressedDirectory();
    
    function removeUncompressedDirectory();
    
    function compress();
    
    function uncompress();
}