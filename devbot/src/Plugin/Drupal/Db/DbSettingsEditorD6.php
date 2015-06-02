<?php
namespace Devbot\Plugin\Drupal\Db;

use Devbot\Plugin\Drupal\Db\MysqlSettings;
use Devbot\Plugin\Drupal\VersionDetector;

/**
 * Database settings editor for Drupal 6
 */
class DbSettingsEditorD6 extends DbPhpFileSettingsEditor
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
        
        if (!isset ($variables['db_url'])) {
            return;
        }
        
        $output = new MysqlSettings();
        
        if (isset ($variables['db_prefix'])) {
            $output->setPrefix($variables['db_prefix']);
        }
        
        $dbUrl = $variables['db_url'];
        $dbUrlParts = parse_url($dbUrl);
        if ($dbUrlParts === false) {
            return;
        }
        
        if (isset ($dbUrlParts['host'])) {
            $output->setHost($dbUrlParts['host']);
        }
        if (isset ($dbUrlParts['user'])) {
            $output->setUsername($dbUrlParts['user']);
        }
        if (isset ($dbUrlParts['pass'])) {
            $output->setPassword($dbUrlParts['pass']);
        }
        if (isset ($dbUrlParts['port'])) {
            $output->setPort($dbUrlParts['port']);
        }
        if (isset ($dbUrlParts['path'])) {
            $output->setDatabase(substr($dbUrlParts['path'], 1));
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
        
        $modified = [];
        
        $dbUrlParts = ['mysqli://'];
        $dbUrlParts[] = $settings->getUsername();
        $dbUrlParts[] = ':';
        $dbUrlParts[] = $settings->getPassword();
        $dbUrlParts[] = '@';
        $dbUrlParts[] = $settings->getHost();
        if ($port = $settings->getPort()) {
            $dbUrlParts[] = ':';
            $dbUrlParts[] = $port;
        }
        $dbUrlParts[] = '/';
        $dbUrlParts[] = $settings->getDatabase();
        $modified['db_url'] = implode('', $dbUrlParts);
        $modified['db_prefix'] = $settings->getPrefix();
        
        $editor->setModifiedVariables($modified);
        
        $this->writeSettingsFromEditor($editor);
    }
    
    /**
     * {@inheritdoc}
     */
    function supportsDrupalVersion($version)
    {
        return $version === VersionDetector::VERSION_6;
    }
}