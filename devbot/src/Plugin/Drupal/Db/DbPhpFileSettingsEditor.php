<?php
namespace Devbot\Plugin\Drupal\Db;

use Devbot\Core\Settings\PhpSettingsEditor;

use League\Flysystem\Filesystem;

abstract class DbPhpFileSettingsEditor implements DbSettingsEditorInterface
{
    const DEFAULT_SETTINGS_PATH = 'public/sites/default/settings.php';
    
    /**
     * @var Filesystem
     */
    protected $siteFilesystem;
    
    /**
     * {@inheritdoc}
     */
    public function setSiteFilesystem(Filesystem $filesystem)
    {
        $this->siteFilesystem = $filesystem;
    }
    
    /**
     * Get an editor for a PHP settings file
     * 
     * @param string|null $path Path of the file, or null for the default path
     * @return PhpSettingsEditor|null Editor, or null if the file was not found
     */
    public function getPhpSettingsEditor($path = null)
    {
        if ($path === null) {
            $path = self::DEFAULT_SETTINGS_PATH;
        }
        
        $fs = $this->siteFilesystem;
        
        if (!$fs->has($path)) {
            return null;
        }
        
        $settingsFileData = $fs->read($path);
        $settingsEditor = new PhpSettingsEditor($settingsFileData);
        
        return $settingsEditor;
    }
}