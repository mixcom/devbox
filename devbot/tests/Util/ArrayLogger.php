<?php
class ArrayLogger extends Psr\Log\AbstractLogger
{
    public $messages = [];
    
    public function log($level, $message, array $context = array())
    {
        $this->messages[] = [$level, $message, $context];
    }
}
