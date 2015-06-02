<?php
namespace Devbot\Install;

use Devbot\Install\Filesystem\Helper as FilesystemHelper;

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\EmptyDir;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

use Psr\Log\LoggerAwareTrait;

class ArchiveZipCompressor implements ArchiveCompressorInterface
{
    use LoggerAwareTrait;
    
    const PREFIX_COMPRESSED   = 'compressed';
    const PREFIX_UNCOMPRESSED = 'uncompressed';
    
    protected $compressedPath;
    protected $uncompressedDirectory;
    
    public function setCompressedPath($path)
    {
        $this->compressedPath = $path;
    }
    
    public function setUncompressedDirectory($directory)
    {
        $this->uncompressedDirectory = $directory;
    }
    
    public function prepareEmptyUncompressedDirectory()
    {
        $fs = $this->getUncompressedFilesystem();
        $fs->addPlugin(new EmptyDir);
        $fs->emptyDir('');
    }
    
    public function removeUncompressedDirectory()
    {
        $fs = new Filesystem(new Local(dirname($this->uncompressedDirectory)));
        $fs->deleteDir(basename($this->uncompressedDirectory));
    }
    
    public function hasArchive()
    {
        list ($fs, $name) = $this->_compressedDirectoryFilesystem();
        return $fs->has($name);
    }
    
    public function compress()
    {
        $this->prepareEmptyAvailableCompressedPath();
        
        $fs = $this->getMountManager();
        $fsHelper = new FilesystemHelper($fs);
        
        $fsHelper->copyDirectory(
            self::PREFIX_UNCOMPRESSED . '://',
            self::PREFIX_COMPRESSED   . '://'
        );
        
        // make sure the ZIP file is properly closed
        $fs->getFilesystem(self::PREFIX_COMPRESSED)
            ->getAdapter()->getArchive()->close();
    }
    
    public function uncompress()
    {
        $this->prepareEmptyUncompressedDirectory();
        
        if (!$this->hasArchive()) {
            throw new \RuntimeException('Archive does not exist');
        }
        
        $fs = $this->getMountManager();
        $fsHelper = new FilesystemHelper($fs);
        
        $fsHelper->copyDirectory(
            self::PREFIX_COMPRESSED   . '://',
            self::PREFIX_UNCOMPRESSED . '://'
        );
    }
    
    protected function getUncompressedFilesystem()
    {
        return new Filesystem(new Local($this->uncompressedDirectory));
    }
    
    protected function getCompressedFilesystem()
    {
        $path = $this->getFullCompressedPath();
        return new Filesystem(new ZipArchiveAdapter($path));
    }
    
    protected function getFullCompressedPath()
    {
        return $this->compressedPath . '.zip';
    }
    
    protected function getMountManager()
    {
        $manager = new MountManager([
            self::PREFIX_COMPRESSED   => $this->getCompressedFilesystem(),
            self::PREFIX_UNCOMPRESSED => $this->getUncompressedFilesystem(),
        ]);
        return $manager;
    }
    
    protected function prepareEmptyAvailableCompressedPath()
    {
        list ($fs, $name) = $this->_compressedDirectoryFilesystem();
        if ($fs->has($name)) {
            $fs->delete($name);
        }
    }
    
    private function _compressedDirectoryFilesystem()
    {
        $path = $this->getFullCompressedPath();
        $name = basename($path);
        $dir = dirname($path);
        $fs = new Filesystem(new Local($dir));
        return [$fs, $name];
    }
}