<?php
namespace Devbot\Site\Console\Command;

use Devbot\Site\VcsClone\ClonerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

class CloneCommand extends Command
{
    const OPT_SOURCE             = 'source';
    const OPT_TARGET             = 'target';
    const OPT_DIR                = 'dir';
    const OPT_BRANCH             = 'branch';
    
    const DEFAULT_DIR            = '/var/www/sites';
    
    protected $cloner;
    
    public function setCloner(ClonerInterface $cloner)
    {
        $this->cloner = $cloner;
        return $this;
    }
    
    protected function configure()
    {
        $this
            ->setName('clone')
            ->setDescription(
                'Create a local clone of a website from a Git repository'
            )
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
            ->addOption(
                self::OPT_BRANCH,
                'b',
                InputOption::VALUE_REQUIRED,
                'Clone a specific branch of the repository'
            )
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cloner->setLogger(new ConsoleLogger($output));
        
        $this->configureClonerFromInput($this->cloner, $input);
        $this->cloner->runClone();
    }
    
    public function configureClonerFromInput(
        ClonerInterface $cloner, 
        InputInterface $input
    ) {
        $source = $input->getArgument(self::OPT_SOURCE);
        $target = $input->getArgument(self::OPT_TARGET);
        
        $cloner->setSource($source);
        
        if ($target === null) {
            $dir = $input->getOption(self::OPT_DIR);
            $cloner->deriveTargetFromSourceInDirectory($dir);
        } else {
            $cloner->setTarget($target);
        }
        
        $branch = $input->getOption(self::OPT_BRANCH);
        if ($branch !== null) {
            $cloner->setBranch($input->getOption(self::OPT_BRANCH));
        }
    }
}