<?php
namespace Devbot\Site\Console\Command;

use Devbot\Install\InstallerInterface;
use Devbot\Site\VcsClone\ClonerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

class SetupCommand extends Command
{
    const OPT_SOURCE             = 'source';
    const OPT_TARGET             = 'target';
    const OPT_DIR                = 'dir';
    const OPT_BRANCH             = 'branch';
    const OPT_ARCHIVE            = 'archive';
    
    const DEFAULT_DIR            = '/var/www/sites';
    const DEFAULT_ARCHIVE        = 'develop';
    
    protected $cloner;
    protected $installer;
    
    public function setCloner(ClonerInterface $cloner)
    {
        $this->cloner = $cloner;
        return $this;
    }
    
    public function setInstaller(InstallerInterface $installer)
    {
        $this->installer = $installer;
        return $this;
    }
    
    protected function configure()
    {
        $this
            ->setName('setup')
            ->setDescription(
                'Clone a website and set it up (same as running clone + install)'
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
            ->addOption(
                self::OPT_ARCHIVE,
                'a',
                InputOption::VALUE_REQUIRED,
                'Install a specific archive',
                self::DEFAULT_ARCHIVE
            )
        ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        
        $this->cloner->setLogger($logger);
        $this->installer->setLogger($logger);
        
        $target = $this->configureClonerFromInput($this->cloner, $input);
        $this->configureInstallerFromInput($this->installer, $input, $target);
        
        $this->cloner->runClone();
        $this->installer->install();
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
            $target = $cloner->deriveTargetFromSourceInDirectory($dir);
        } else {
            $cloner->setTarget($target);
        }
        
        $branch = $input->getOption(self::OPT_BRANCH);
        if ($branch !== null) {
            $cloner->setBranch($input->getOption(self::OPT_BRANCH));
        }
        
        return $target;
    }
    
    public function configureInstallerFromInput(
        InstallerInterface $installer, 
        InputInterface $input,
        $directory
    ) {
        $installer->setDirectory($directory);
        
        $archive = $input->getOption(self::OPT_ARCHIVE);
        if ($archive !== null) {
            $installer->setArchive($archive);
        }
    }
}