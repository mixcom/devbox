<?php
namespace Devbot\Core\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;

use Devbot\Core\Task\TaskInterface;

abstract class Command extends BaseCommand
{
    protected $task;
    
    public function __construct(TaskInterface $task)
    {
        parent::__construct();
        $this->task = $task;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->task->setLogger($this->getDefaultLogger($output));
        $this->configureTaskFromInput($input);
        $this->task->run();
    }
    
    protected function getDefaultLogger(OutputInterface $output)
    {
        $logger = new ConsoleLogger($output,[
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE,
        ]);
        return $logger;
    }
    
    protected function configureTaskFromInput(InputInterface $input)
    {
    }
}