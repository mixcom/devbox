<?php
namespace Devbot\Plugin\Drupal\Install\Plugin;

use Devbot\Plugin\Drupal\VersionDetector as DrupalVersionDetector;
use Devbot\Plugin\Drupal\Db\MysqlSettings;
use Devbot\Plugin\Drupal\Db\DbSettingsEditorInterface;
use Devbot\Install\QuestionManagerInterface;
use Devbot\Install\Plugin\AbstractPlugin;
use Devbot\Install\Plugin\PluginEnvironment;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Console\Question\Question;

class Db extends AbstractPlugin
{
    const DEFAULT_HOST = 'mysql';
    const DEFAULT_USERNAME = 'root';
    const DEFAULT_PASSWORD = '';
    const DEFAULT_PORT = 3306;
    const DEFAULT_DATABASE = '{site}_drupal';
    const DEFAULT_PREFIX = '';
    
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
        $versionDetector = new DrupalVersionDetector();
        $version = $versionDetector->detectMajorVersion($env->getSiteFilesystem());
        
        if ($version === DrupalVersionDetector::NOT_DRUPAL) {
            return;
        }
        
        $settingsEditor = $this->getDbSettingsEditorForVersion($version);
        if ($settingsEditor === null) {
            $this->logger->warning(
                'Unsupported Drupal version: {version}',
                ['version' => $version]
            );
            return;
        }
        
        $settingsEditor->setSiteFilesystem($env->getSiteFilesystem());
        $mysqlSettings = $settingsEditor->getMysqlSettings();
        
        if ($mysqlSettings === null) {
            $this->logger->warning('Can\'t archive Drupal DB: no MySQL settings');
            return;
        }
        if (!$this->checkMysqlSettingsComplete($mysqlSettings)) {
            $this->logger->warning(
                'Can\'t archive Drupal DB: incomplete DB settings'
            );
            return;
        }
        
        // make sure the dump directory exists
        $env->getDumpFilesystem();
        
        // get the dump process
        $dumpFile = $env->getDumpDirectory() . DIRECTORY_SEPARATOR . 'db.sql';
        $backupProcess = $this->getMysqldumpProcess(
            $env->getProcessBuilder(),
            $mysqlSettings, 
            $dumpFile
        );
        
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
        
        if ($version === DrupalVersionDetector::NOT_DRUPAL) {
            return;
        }
        
        $settingsEditor = $this->getDbSettingsEditorForVersion($version);
        if ($settingsEditor === null) {
            $this->logger->warning(
                'Unsupported Drupal version: {version}',
                ['version' => $version]
            );
            return;
        }
        
        $settingsEditor->setSiteFilesystem($env->getSiteFilesystem());
        $mysqlSettings = $settingsEditor->getMysqlSettings();
        
        $updateSettings = false;
        
        if ($mysqlSettings === null) {
            $mysqlSettings = new MysqlSettings();
        }
        if (!$this->checkMysqlSettingsComplete($mysqlSettings)) {
            
            $questionManager = $env->getQuestionManager();
            if ($questionManager === null) {
                $this->logger->warning(
                    'Can\'t install DB: incomplete settings and can\'t ask'
                );
                return;
            }
            
            $defaultSettings = $this->getDefaultMysqlSettings($env);
            
            $mysqlSettings = $this->askMysqlSettings(
                $questionManager,
                $mysqlSettings,
                $defaultSettings
            );
            
            $updateSettings = true;
        }
        
        $this->createDatabaseIfNotExists($mysqlSettings);
        
        // get the import process
        $dumpFile = $env->getDumpDirectory() . DIRECTORY_SEPARATOR . 'db.sql';
        if (file_exists($dumpFile)) {
            $importProcess = $this->getMysqlImportProcess(
                $env->getProcessBuilder(),
                $mysqlSettings, 
                $dumpFile
            );
            
            // run it
            $this->logger->info(
                'Importing database {db}', 
                [
                    'db' => $mysqlSettings->getDatabase(),
                ]
            );
            $importProcess->run();
        }
        
        // auto detect DB prefix
        $prefix = $this->detectDbPrefix($mysqlSettings);
        if ($prefix && !$mysqlSettings->getPrefix()) {
            $mysqlSettings->setPrefix($prefix);
            $updateSettings = true;
        }
        
        if ($updateSettings) {
            $settingsEditor->setMysqlSettings($mysqlSettings);
        }
    }
    
    public function checkMysqlSettingsComplete(MysqlSettings $mysqlSettings)
    {
        return $mysqlSettings->getHost() !== null
            && $mysqlSettings->getDatabase() !== null;
    }
    
    public function getMysqldumpProcess(
        ProcessBuilder $processBuilder,
        MysqlSettings $mysqlSettings, 
        $dumpFile = null
    ) {
        $args = $this->getMysqldumpProcessArgs($mysqlSettings, $dumpFile);
        $processBuilder->setArguments($args);
        $process = $processBuilder->getProcess();
        return $process;
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
    
    public function getMysqlImportProcess(
        ProcessBuilder $processBuilder,
        MysqlSettings $mysqlSettings, 
        $dumpFile = null
    ) {
        $args = $this->getMysqlImportProcessArgs($mysqlSettings, $dumpFile);
        $processBuilder->setArguments($args);
        $process = $processBuilder->getProcess();
        return $process;
    }
    
    public function getMysqlImportProcessArgs(
        MysqlSettings $mysqlSettings, 
        $dumpFile
    ) {
        $database = $mysqlSettings->getDatabase();
        
        if ($database === null) {
            throw new \RuntimeException('No database specified');
        }
        $args = ['mysql'];
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
        $args[] = $database;
        $args[] = '<';
        $args[] = $dumpFile;
        
        $shellArgs = ['/bin/sh', '-c'];
        $shellArgs[] = implode(' ', $args);
        
        return $shellArgs;
    }
    
    public function askMysqlSettings(
        QuestionManagerInterface $questionManager,
        MysqlSettings $currentSettings,
        MysqlSettings $defaultSettings
    ) {
        $settings = clone $currentSettings;
        
        if ($settings->getHost() === null) {
          $question = $this->textQuestion(
              'MySQL host', 
              $defaultSettings->getHost()
          );
          $settings->setHost($questionManager->askQuestion($question));
        }
        if ($settings->getUsername() === null) {
          $question = $this->textQuestion(
              'MySQL username', 
              $defaultSettings->getUsername()
          );
          $settings->setUsername($questionManager->askQuestion($question));
        }
        if ($settings->getPassword() === null) {
          $question = $this->textQuestion(
              'MySQL password', 
              $defaultSettings->getPassword()
          );
          $question->setHidden(true);
          $settings->setPassword($questionManager->askQuestion($question));
        }
        if ($settings->getPort() === null) {
          $question = $this->textQuestion(
              'MySQL port', 
              $defaultSettings->getPort()
          );
          $settings->setPort($questionManager->askQuestion($question));
        }
        if ($settings->getDatabase() === null) {
          $question = $this->textQuestion(
              'MySQL database', 
              $defaultSettings->getDatabase()
          );
          $settings->setDatabase($questionManager->askQuestion($question));
        }
        if ($settings->getPrefix() === null) {
          $question = $this->textQuestion(
              'MySQL prefix, autodetect if left blank', 
              $defaultSettings->getPrefix()
          );
          $settings->setPrefix($questionManager->askQuestion($question));
        }
        
        return $settings;
    }
    
    protected function textQuestion($text, $default)
    {
        $text .= ' (' . $default . '): ';
        
        $question = new Question($text, $default);
        
        return $question;
    }
    
    public function getDefaultMysqlSettings(PluginEnvironment $env) {
        $settings = new MysqlSettings();
        
        $settings
            ->setHost(self::DEFAULT_HOST)
            ->setUsername(self::DEFAULT_USERNAME)
            ->setPassword(self::DEFAULT_PASSWORD)
            ->setPort(self::DEFAULT_PORT)
            ->setPrefix(self::DEFAULT_PREFIX);
        
        
        $dir = $env->getSiteDirectory();
        $site = basename($dir);
        $site = preg_replace('([^a-zA-Z0-9_]+)s', '_', $site);
        $database = str_replace('{site}', $site, self::DEFAULT_DATABASE);
        $settings->setDatabase($database);
        
        return $settings;
    }
    
    public function createDatabaseIfNotExists(MysqlSettings $settings)
    {
        $dbLessSettings = clone $settings;
        $dbLessSettings->setDatabase(null);
        
        $db = $this->getPdoConnection($dbLessSettings);
        
        $database = $settings->getDatabase();
        $query = "CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARSET utf8";
        $db->exec($query);
    }
    
    public function detectDbPrefix(MysqlSettings $settings)
    {
        $db = $this->getPdoConnection($settings);
        
        $st = $db->prepare("SHOW TABLES");
        $st->execute();
        
        $result = $st->fetchAll(\PDO::FETCH_COLUMN, 0);
        return $this->detectDbPrefixFromTableNames($result);
    }
    
    protected function getPdoConnection(MysqlSettings $settings)
    {
        $dsnParts = [];
        $dsnParts[] = 'host=' . $settings->getHost();
        $dsnParts[] = 'port=' . $settings->getPort();
        $dsnParts[] = 'charset=utf8';
        if ($database = $settings->getDatabase()) {
            $dsnParts[] = 'dbname=' . $database;
        }
        
        $dsn = 'mysql:' . implode(';', $dsnParts);
        $db = new \PDO($dsn, $settings->getUsername(), $settings->getPassword());
        
        return $db;
    }
    
    public function detectDbPrefixFromTableNames(array $tables)
    {
        $thresholdFraction = 0.9;
        
        $numTables = sizeof($tables);
        
        $lengths = array_map('strlen', $tables);
        $minLength = min($lengths);
        for ($length = $minLength; $length > 0; $length--) {
            $prefixes = [];
            foreach ($tables as $table) {
                $prefix = substr($table, 0, $length);
                if (!isset ($prefixes[$prefix])) {
                    $prefixes[$prefix] = 0;
                }
                $prefixes[$prefix]++;
            }
            asort($prefixes);
            foreach (array_reverse($prefixes) as $prefix => $count) {
                if ($count / $numTables >= $thresholdFraction) {
                    return $prefix;
                }
            }
        }
    }
}