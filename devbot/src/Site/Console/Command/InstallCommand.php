<?php
namespace Devbot\Site\Console\Command;

use Devbot\Install\InstallerInterface;
use Devbot\Install\ConsoleQuestionManager;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;


class InstallCommand extends Command
{
    const OPT_DIR                = 'dir';
    const OPT_ARCHIVE            = 'archive';
    
    const DEFAULT_DIR            = '.';
    const DEFAULT_ARCHIVE        = 'develop';
    
    protected $installer;
    
    public function setInstaller(InstallerInterface $installer)
    {
        $this->installer = $installer;
    }
    
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Install all local content to get a site working and ready for development')
            ->addArgument(
                self::OPT_DIR,
                InputArgument::OPTIONAL,
                'Install the site located in this directory',
                self::DEFAULT_DIR
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
        $this->installer->setLogger($logger);
        
        $this->configureInstallerFromInput($this->installer, $input);
        
        $questionManager = $this->getQuestionManager($input, $output);
        $this->installer->setQuestionManager($questionManager);
        
        $this->installer->install();
    }
    
    public function configureInstallerFromInput(
        InstallerInterface $installer, 
        InputInterface $input
    ) {
        $directory = $input->getArgument(self::OPT_DIR);
        $installer->setDirectory($directory);
        
        $archive = $input->getOption(self::OPT_ARCHIVE);
        if ($archive !== null) {
            $installer->setArchive($archive);
        }
    }
    
    public function getQuestionManager(
        InputInterface $input, 
        OutputInterface $output
    ) {
        $questionHelper = $this->getHelper('question');
        if ($questionHelper !== null) {
          $questionManager = new ConsoleQuestionManager($questionHelper);
          $questionManager
            ->setInput($input)
            ->setOutput($output)
          ;
          return $questionManager;
        }
    }
}