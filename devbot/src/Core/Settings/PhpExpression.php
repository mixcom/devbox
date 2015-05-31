<?php
namespace Devbot\Core\Settings;

class PhpExpression
{
    protected $expression;
    
    public function __construct($expression)
    {
        $this->expression = $expression;
    }
    
    public function __toString()
    {
        return $this->expression;
    }
}