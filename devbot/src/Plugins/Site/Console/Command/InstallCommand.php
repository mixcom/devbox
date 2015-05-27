<?php
namespace Devbot\Plugins\Site\Console\Command;

use Devbot\Core\Console\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

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
    
    protected function configureTaskFromInput(InputInterface $input)
    {
        $directory = $input->getArgument(self::OPT_DIR);
        $this->task->setDirectory($directory);
        
        if ($input->getOption(self::OPT_FORCE)) {
            $this->task->setForce(true);
        }
    }
}