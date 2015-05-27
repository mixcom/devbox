<?php
namespace Devbot\Plugins\Site\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use Devbot\Plugins\Site\Task\InstallTask;

class InstallCommand extends Command
{
    const OPT_DIR                = 'dir';
    const OPT_FORCE              = 'force';
    
    const DEFAULT_DIR            = '.';
    
    protected function configure()
    {
        $this
            ->setName('site:install')
            ->setDescription('Install all local content to get a site working and ready for development')
            ->addArgument(
                self::OPT_DIR,
                InputArgument::OPTIONAL,
                'Set up the site in this directory',
                self::DEFAULT_DIR
            )
            ->addOption(
                self::OPT_FORCE,
                'f',
                InputOption::VALUE_NONE,
                'Force setup, even if the site is already set up'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output,[
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE,
        ]);
        
        $task = self::buildTaskFromInput($input, $logger);
        $task->run();
    }
    
    protected static function buildTaskFromInput(InputInterface $input, LoggerInterface $logger)
    {
        $directory = $input->getArgument(self::OPT_DIR);
        $task = new InstallTask($directory, $logger);
        
        if ($input->getOption(self::OPT_FORCE)) {
            $task->setForce(true);
        }
        
        return $task;
    }
}