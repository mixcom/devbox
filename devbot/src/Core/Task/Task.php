<?php
namespace Devbot\Core\Task;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Task implements TaskInterface
{
    protected $logger;
    
    public function getLogger()
    {
        return $this->logger;
    }
    
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }
    
    public function __construct(LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger;
        }
        $this->setLogger($logger);
    }
}