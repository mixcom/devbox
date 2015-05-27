<?php
namespace Devbot\Plugins\Site\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

use Devbot\Plugins\Site\Task\GitCloneTask;
use Devbot\Plugins\Site\Task\InstallTask;
use Devbot\Core\Task\QueueTask;

class SetupCommand extends Command
{
    const OPT_SOURCE             = 'source';
    const OPT_TARGET             = 'target';
    const OPT_DIR                = 'dir';
    
    const DEFAULT_DIR            = '/var/www/sites';
    
    protected function configure()
    {
        $this
            ->setName('site:setup')
            ->setDescription('Clone a website and set it up (same as running site:clone + site:install)')
            ->addArgument(
                self::OPT_SOURCE,
                InputArgument::REQUIRED,
                'Clone from this location'
            )
            ->addArgument(
                self::OPT_TARGET,
                InputArgument::OPTIONAL,
                'Clone into this directory'
            )
            ->addOption(
                self::OPT_DIR,
                'd',
                InputOption::VALUE_REQUIRED,
                'Clone into an auto-named subdirectory of this directory',
                self::DEFAULT_DIR
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
        $cloneTask = self::buildGitCloneTaskFromInput($input, $logger);
        $directory = $cloneTask->getTarget();
        $installTask = self::buildInstallTask($directory, $logger);
        
        $task = new QueueTask([
            $cloneTask,
            $installTask,
        ], $logger);
        
        return $task;
    }
    
    protected static function buildGitCloneTaskFromInput(InputInterface $input, LoggerInterface $logger)
    {
        $source = $input->getArgument(self::OPT_SOURCE);
        $target = $input->getArgument(self::OPT_TARGET);
        if ($target === null) {
            $dir = $input->getOption(self::OPT_DIR);
            $target = GitCloneTask::autoTarget($source, $dir);
        }
        
        $task = new GitCloneTask($source, $target, $logger);
        return $task;
    }
    
    protected static function buildInstallTask($directory, LoggerInterface $logger)
    {
        $task = new InstallTask($directory, $logger);
        return $task;
    }
}