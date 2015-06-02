<?php
namespace Devbot\Plugin\Drupal\Db;

use Devbot\Plugin\Drupal\Db\MysqlSettings;
use Devbot\Plugin\Drupal\VersionDetector;

/**
 * Database settings editor for Drupal 7
 */
class DbSettingsEditorD7 extends DbPhpFileSettingsEditor
{
    /**
     * {@inheritdoc}
     */
    function getMysqlSettings()
    {
        $settings = $this->getPhpSettingsEditor();
        if (!$settings) {
            return null;
        }
        
        $variables = $settings->getOriginalVariables();
        
        if (!isset ($variables['databases']['default']['default'])) {
            return;
        }
        
        $output = new MysqlSettings();
        
        $dbSettings = $variables['databases']['default']['default'];
        if (isset ($dbSettings['host'])) {
            $output->setHost($dbSettings['host']);
        }
        if (isset ($dbSettings['username'])) {
            $output->setUsername($dbSettings['username']);
        }
        if (isset ($dbSettings['password'])) {
            $output->setPassword($dbSettings['password']);
        }
        if (isset ($dbSettings['port'])) {
            $output->setPort($dbSettings['port']);
        }
        if (isset ($dbSettings['database'])) {
            $output->setDatabase($dbSettings['database']);
        }
        if (isset ($dbSettings['prefix'])) {
            $output->setPrefix($dbSettings['prefix']);
        }
        return $output;
    }
    
    /**
     * {@inheritdoc}
     */
    function setMysqlSettings(MysqlSettings $settings)
    {
        $editor = $this->getPhpSettingsEditor();
        if (!$editor) {
            throw new \RuntimeException('Can\'t edit settings');
        }
        
        $variables = $editor->getOriginalVariables();
        $databases = [];
        if (isset ($variables['databases'])) {
            $databases = $variables['databases'];
        }
        if (!isset ($databases['default']['default'])) {
            $databases['default']['default'] = [];
        }
        $databaseSettings = $databases['default']['default'];
        $databaseSettings['host'] = $settings->getHost();
        $databaseSettings['username'] = $settings->getUsername();
        $databaseSettings['password'] = $settings->getPassword();
        if ($port = $settings->getPort()) {
            $databaseSettings['port'] = $port;
        }
        $databaseSettings['database'] = $settings->getDatabase();
        $databaseSettings['prefix'] = $settings->getPrefix();
        
        $databases['default']['default'] = $databaseSettings;
        
        $editor->setModifiedVariables(['databases' => $databases]);
        
        $this->writeSettingsFromEditor($editor);
    }
    
    /**
     * {@inheritdoc}
     */
    function supportsDrupalVersion($version)
    {
        return $version === VersionDetector::VERSION_7;
    }
}