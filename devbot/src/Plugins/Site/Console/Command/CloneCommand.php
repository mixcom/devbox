<?php
namespace Devbot\Plugins\Site\Console\Command;

use Devbot\Core\Console\Command\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class CloneCommand extends Command
{
    const OPT_SOURCE             = 'source';
    const OPT_TARGET             = 'target';
    const OPT_DIR                = 'dir';
    
    const DEFAULT_DIR            = '/var/www/sites';
    
    protected function configure()
    {
        $this
            ->setName('site:clone')
            ->setDescription('Create a local clone of a website from a Git repository')
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
        $source = $input->getArgument(self::OPT_SOURCE);
        $target = $input->getArgument(self::OPT_TARGET);
        if ($target === null) {
            $dir = $input->getOption(self::OPT_DIR);
            $target = $this->task->autoTarget($source, $dir);
        }
        
        $this->task->setSource($source);
        $this->task->setTarget($target);
    }
}