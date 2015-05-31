<?php
namespace Devbot\Plugin\Drupal;

use League\Flysystem\Filesystem;

class VersionDetector
{
    const VERSION_6 = '6.x';
    const VERSION_7 = '7.x';
    const VERSION_8 = '8.x';
    const NOT_DRUPAL = 'none';
    
    public function detectMajorVersion(Filesystem $fs)
    {
        $bootstrapFile = 'public/index.php';
        
        if (!$fs->has($bootstrapFile)) {
            return self::NOT_DRUPAL;
        }
        
        $bootstrapData = $fs->read($bootstrapFile);
        return $this->detectMajorVersionFromBootstrapData($bootstrapData);
    }
    
    public function detectMajorVersionFromBootstrapData($bootstrapData)
    {
        if (strpos($bootstrapData, 'Drupal\Core\DrupalKernel') !== false) {
            return self::VERSION_8;
        }
        if (strpos($bootstrapData, 'DRUPAL_ROOT') !== false) {
            return self::VERSION_7;
        }
        if (strpos($bootstrapData, 'drupal_page_footer') !== false) {
            return self::VERSION_6;
        }
        return self::NOT_DRUPAL;
    }
}