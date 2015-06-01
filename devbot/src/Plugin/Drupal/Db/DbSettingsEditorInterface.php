<?php
namespace Devbot\Plugin\Drupal\Db;

use League\Flysystem\Filesystem;

/**
 * Editors that implement this interface allow you to change Drupal database settings
 */
interface DbSettingsEditorInterface
{
    /**
     * Set the filesystem where the settings can be found
     * 
     * @param Filesystem $filesystem
     */
    function setSiteFilesystem(Filesystem $filesystem);
    
    /**
     * Get the MySQL settings from the site
     * 
     * If none can be found, returns null.
     * 
     * @return MysqlSettings|null
     */
    function getMysqlSettings();
    
    /**
     * Update the MySQL settings for the site
     * 
     * @param MysqlSettings $settings
     */
    function setMysqlSettings(MysqlSettings $settings);
    
    /**
     * Check if this editor supports the specified Drupal version
     * 
     * @param string $version Any of Devbot\Plugin\Drupal\VersionDetector::VERSION_*
     * @return bool
     */
    function supportsDrupalVersion($version);
}