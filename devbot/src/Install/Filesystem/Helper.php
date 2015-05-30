<?php
namespace Devbot\Install\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

final class Helper
{
    protected $filesystem;
    
    public function __construct($filesystem)
    {
        if (   !($filesystem instanceof Filesystem)
            && !($filesystem instanceof MountManager)) {
            throw new \InvalidArgumentException(
                'Can only handle Filesystem or MountManager objects'
            );
        }
        
        $this->filesystem = $filesystem;
    }
    
    public function copyDirectory($source, $target)
    {
        $source = $this->normalizePath($source);
        $target = $this->normalizePath($target);
        
        $contents = $this->filesystem->listContents($source, true);
        
        foreach ($contents as $item) {
            $path = $item['path'];
            
            if ($item['filesystem']) {
                $path = $item['filesystem'] . '://' . $path;
            }
            
            $targetPath = $this->combinePath([
                $target, 
                $this->relativePath($path, $source)
            ]);
            
            switch ($item['type']) {
                case 'dir':
                  $this->filesystem->createDir($targetPath);
                  break;
                  
                case 'file':
                  if ($this->filesystem->has($targetPath)) {
                      $this->filesystem->delete($targetPath);
                  }
                  $this->filesystem->copy($path, $targetPath);
                  break;
            }
        }
    }
    
    public function normalizePath($path)
    {
        if (substr($path, -3) !== '://' && substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
        }
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        return $path;
    }
    
    public function relativePath($path, $base)
    {
        if (substr($base, -1) === '/') {
            return substr($path, strlen($base));
        }
        return substr($path, strlen($base) + 1);
    }
    
    public function combinePath(array $parts)
    {
        $separator = '/';
        
        $output = [];
        $size = sizeof($parts);
        for ($i = 0; $i < $size; $i++) {
            $last = ($i == $size - 1);
            $part = $parts[$i];
            
            $output[] = $part;
            if (!$last && substr($part, -1) != $separator) {
                $output[] = $separator;
            }
        }
        
        return implode('', $output);
    }
}