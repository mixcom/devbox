<?php
namespace Devbot\Plugins\Site\Console\Command;

use Devbot\Core\Console\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

use Devbot\Plugins\Site\Task\SetupTask;

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
    
    protected function configureTaskFromInput(InputInterface $input)
    {
        $gitCloneTask = $this->task->getGitCloneTask();
        
        $source = $input->getArgument(self::OPT_SOURCE);
        $target = $input->getArgument(self::OPT_TARGET);
        if ($target === null) {
            $dir = $input->getOption(self::OPT_DIR);
            $target = $gitCloneTask->autoTarget($source, $dir);
        }
        $gitCloneTask->setSource($source);
        $gitCloneTask->setTarget($target);
        
        $installTask = $this->task->getInstallTask();
        $installTask->setDirectory($target);
    }
}