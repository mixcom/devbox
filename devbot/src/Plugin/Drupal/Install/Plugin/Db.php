<?php
namespace Devbot\Plugin\Drupal\Install\Plugin;

use Devbot\Plugin\Drupal\VersionDetector as DrupalVersionDetector;
use Devbot\Plugin\Drupal\Db\MysqlSettings;
use Devbot\Plugin\Drupal\Db\DbSettingsEditorInterface;
use Devbot\Install\Plugin\AbstractPlugin;
use Devbot\Install\Plugin\PluginEnvironment;

use Symfony\Component\Process\ProcessBuilder;

class Db extends AbstractPlugin
{
    protected $settingsEditors = [];
    
    public function getPluginId()
    {
        return 'drupal-db';
    }
    
    public function setDbSettingsEditors(array $editors)
    {
        $this->settingsEditors = [];
        foreach ($editors as $editor) {
            $this->addDbSettingsEditor($editor);
        }
        return $this;
    }
    
    public function addDbSettingsEditor(DbSettingsEditorInterface $editor)
    {
        $this->settingsEditors[] = $editor;
    }
    
    public function getDbSettingsEditorForVersion($version)
    {
        foreach ($this->settingsEditors as $editor) {
            if ($editor->supportsDrupalVersion($version)) {
                return $editor;
            }
        }
    }
    
    public function archive(PluginEnvironment $env)
    {
        $mysqlSettings = $this->getMysqlSettingsForEnvironment($env);
        
        if ($mysqlSettings === null) {
            return;
        }
        if (!$mysqlSettings->getHost() || !$mysqlSettings->getDatabase()) {
            $this->logger->warning(
                'Can\'t archive Drupal DB: incomplete DB settings'
            );
            return;
        }
        
        // make sure the dump directory exists
        $env->getDumpFilesystem();
        
        // get the dump process
        $dumpFile = $env->getDumpDirectory() . DIRECTORY_SEPARATOR . 'db.sql';
        $backupProcess = $this->getMysqldumpProcess($mysqlSettings, $dumpFile);
        
        // run it
        $this->logger->info(
            'Archiving database {db}', 
            [
                'db' => $mysqlSettings->getDatabase(),
            ]
        );
        $backupProcess->run();
    }
    
    public function install(PluginEnvironment $env)
    {
        $versionDetector = new DrupalVersionDetector();
        $version = $versionDetector->detectMajorVersion($env->getSiteFilesystem());
        
        switch ($version) {
            case DrupalVersionDetector::NOT_DRUPAL:
                // skip
                return;
            case DrupalVersionDetector::VERSION_6:
                break;
            case DrupalVersionDetector::VERSION_7:
                break;
            default:
                $this->logger->warning(
                    'Unsupported Drupal version: {version}',
                    [
                        'version' => $version,
                    ]
                );
        }
    }
    
    public function getMysqlSettingsForEnvironment(PluginEnvironment $env)
    {
        $versionDetector = new DrupalVersionDetector();
        $version = $versionDetector->detectMajorVersion($env->getSiteFilesystem());
        
        if ($version === DrupalVersionDetector::NOT_DRUPAL) {
            return;
        }
        
        $settingsEditor = $this->getDbSettingsEditorForVersion($version);
        if ($settingsEditor === null) {
            $this->logger->warning(
                'Unsupported Drupal version: {version}',
                [
                    'version' => $version,
                ]
            );
            return;
        }
        
        $settingsEditor->setSiteFilesystem($env->getSiteFilesystem());
        $mysqlSettings = $settingsEditor->getMysqlSettings();
        
        if (!$mysqlSettings) {
            $this->logger->warning('Can\'t archive Drupal DB: no MySQL settings');
        }
        
        return $mysqlSettings;
    }
    
    public function getMysqldumpProcess(
        MysqlSettings $mysqlSettings, 
        $dumpFile = null
    ) {
        $args = $this->getMysqldumpProcessArgs($mysqlSettings, $dumpFile);
        $processBuilder = new ProcessBuilder($args);
        return $processBuilder->getProcess();
    }
    
    public function getMysqldumpProcessArgs(
        MysqlSettings $mysqlSettings, 
        $dumpFile = null
    ) {
        $database = $mysqlSettings->getDatabase();
        
        if ($database === null) {
            throw new \RuntimeException('No database specified');
        }
        $args = ['mysqldump'];
        if ($host = $mysqlSettings->getHost()) {
            $args[] = '-h' . $host;
        }
        if ($user = $mysqlSettings->getUsername()) {
            $args[] = '-u' . $user;
        }
        if ($pass = $mysqlSettings->getPassword()) {
            $args[] = '-p' . $pass;
        }
        if ($port = $mysqlSettings->getPort()) {
            $args[] = '--port=' . $port;
        }
        if ($dumpFile) {
            $args[] = '--result-file=' . $dumpFile;
        }
        $args[] = $database;
        
        return $args;
    }
}