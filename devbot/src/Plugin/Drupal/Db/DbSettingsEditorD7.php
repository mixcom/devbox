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
        
    }
    
    /**
     * {@inheritdoc}
     */
    function supportsDrupalVersion($version)
    {
        return $version === VersionDetector::VERSION_7;
    }
}