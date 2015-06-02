<?php
namespace Devbot\Site\Install;

use Devbot\Install\AbstractInstaller;
use Devbot\Install\ArchiveCompressorInterface;
use Devbot\Install\QuestionManagerInterface;
use Devbot\Install\Plugin\PluginEnvironment;
use Devbot\Install\Plugin\PluginInterface;

use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Process\ProcessBuilder;


class SiteInstaller extends AbstractInstaller
{
    const DUMP_DIRECTORY_PREFIX       = 'dump';
    const ARCHIVE_DIRECTORY_PREFIX    = 'archive';
    
    const DEFAULT_INSTALLER_DIRECTORY = '.devbot/installer';
    
    protected $plugins;
    protected $installerDirectory;
    protected $archiveCompressor;
    protected $questionManager;
    
    public function setProcessBuilder(ProcessBuilder $builder)
    {
        $this->processBuilder = $builder;
        return $this;
    }
    
    public function setInstallerDirectory($directory)
    {
        $this->installerDirectory = $directory;
    }
    
    public function setQuestionManager(QuestionManagerInterface $manager)
    {
        $this->questionManager = $manager;
        return $this;
    }
    
    public function setPlugins(array $plugins)
    {
        $this->plugins = [];
        foreach ($plugins as $plugin) {
            $this->addPlugin($plugin);
        }
        return $this;
    }
    
    public function addPlugin(PluginInterface $plugin)
    {
        $id = $plugin->getPluginId();
        $this->plugins[] = $plugin;
    }
    
    public function setArchiveCompressor(ArchiveCompressorInterface $compressor)
    {
        $this->archiveCompressor = $compressor;
    }
    
    public function __construct()
    {
        $this->plugins = [];
        $this->installerDirectory = self::DEFAULT_INSTALLER_DIRECTORY;
    }
    
    protected function validate()
    {
        if (!isset ($this->directory)) {
            throw new \LogicException('No directory set');
        }
        if (!isset ($this->archive)) {
            throw new \LogicException('No archive name set');
        }
    }
    
    public function install()
    {
        $this->validate();
        
        $this->configureArchiveCompressor($this->archiveCompressor);
        
        $logMessage = 'Installing {directory}';
        if ($this->archiveCompressor->hasArchive()) {
            $this->archiveCompressor->uncompress();
            $logMessage = 'Installing {directory} from {archive}';
        } else {
            $this->logger->warning(
                'Archive {archive} not found', 
                [
                    'archive' => $this->archive,
                ]
            );
        }
        
        $this->logger->info(
            $logMessage,
            [
                'directory' => $this->directory,
                'archive' => $this->archive,
            ]
        );
        
        $this->invokePlugins('install');
        
        $this->archiveCompressor->removeUncompressedDirectory();
    }
    
    public function archive()
    {
        $this->validate();
        
        $this->logger->info(
            'Archiving {directory} to {archive}',
            [
                'directory' => $this->directory,
                'archive' => $this->archive,
            ]
        );
        
        $this->configureArchiveCompressor($this->archiveCompressor);
        $this->archiveCompressor->prepareEmptyUncompressedDirectory();
        
        $this->invokePlugins('archive');
        
        $this->logger->info('Compressing {archive}', ['archive' => $this->archive]);
        
        $this->archiveCompressor->compress();
        $this->archiveCompressor->removeUncompressedDirectory();
    }
    
    protected function invokePlugins($method)
    {
        foreach ($this->plugins as $plugin) {
            $id = $plugin->getPluginId();
            
            $this->logger->info('Running {plugin}', ['plugin' => $id]);
            
            $env = $this->getPluginEnvironment($id);
            
            $plugin->setLogger($this->logger);
            $plugin->{$method}($env);
        }
    }
    
    public function getPluginEnvironment($id)
    {
        $siteDirectory = $this->getDirectoryRealpath();
        $dumpDirectory = $this->getDumpDirectoryForPlugin($id);
        
        $processBuilder = clone $this->processBuilder;
        $processBuilder->setWorkingDirectory($siteDirectory);
        
        $env = new PluginEnvironment(
            $siteDirectory, 
            $dumpDirectory,
            $processBuilder
        );
        
        $env->setQuestionManager($this->questionManager);
        
        return $env;
    }
    
    protected function getDirectoryRealpath()
    {
        $realPath = realpath($this->directory);
        if (!$realPath) {
            throw new \RuntimeException(
                'Directory does not exist: ' . $this->directory
            );
        }
        return $realPath;
    }
    
    protected function getDumpDirectory()
    {
        $dumpDirectory = $this->combinePath([
            $this->getDirectoryRealpath(),
            $this->installerDirectory,
            self::DUMP_DIRECTORY_PREFIX,
        ]);
        return $dumpDirectory;
    }
    
    protected function getDumpDirectoryForPlugin($id)
    {
        $dumpDirectory = $this->combinePath([
            $this->getDumpDirectory(),
            $id,
        ]);
        return $dumpDirectory;
    }
    
    protected function getArchiveDirectory()
    {
        $archiveDir = $this->combinePath([
            $this->getDirectoryRealpath(),
            $this->installerDirectory,
            self::ARCHIVE_DIRECTORY_PREFIX,
        ]);
        return $archiveDir;
    }
    
    protected function getArchivePath()
    {
        $archivePath = $this->combinePath([
            $this->getArchiveDirectory(),
            $this->archive,
        ]);
        return $archivePath;
    }
    
    protected function configureArchiveCompressor(
        ArchiveCompressorInterface $compressor
    ) {
        $uncompressedDirectory = $this->getDumpDirectory();
        $compressor->setUncompressedDirectory($uncompressedDirectory);
        
        $compressedPath = $this->getArchivePath();
        $compressor->setCompressedPath($compressedPath);
    }
    
    protected function combinePath(array $parts)
    {
        return implode(DIRECTORY_SEPARATOR, $parts);
    }
}
