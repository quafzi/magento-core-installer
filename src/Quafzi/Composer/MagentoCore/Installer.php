<?php
/**
 * Magento Core Installer for Composer
 */

namespace Quafzi\Composer\MagentoCore;

use MagentoHackathon\Composer\Magento\Installer as MagentoModuleInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\PackageInterface;
use MagentoHackathon\Composer\Magento\MapParser;
use MagentoHackathon\Composer\Magento\Deploystrategy\Copy as CopyStrategy;

/**
 * Composer Magento Core Installer
 */
class Installer extends MagentoModuleInstaller
{
    public function supports($packageType)
    {
        var_dump(__FILE__ . ' on line ' . __LINE__ . ':', 
            $packageType,
        'magento-core' === $packageType || 'magento-module' === $packageType
        );
        return 'magento-core' === $packageType || 'magento-module' === $packageType;
    }

    /**
     * Install Magento core
     * 
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $package package instance
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        var_dump(__FILE__ . ' on line ' . __LINE__ . ':', $package->getName());
        parent::install($repo, $package);

        $this->prepareMagento($package);
    }

    public function getDeployStrategy(PackageInterface $package, $strategy = null)
    {
        var_dump('forcing copy');
        $targetDir = $this->getTargetDir();
        $sourceDir = $this->getSourceDir($package);
        $impl = new CopyStrategy($sourceDir, $targetDir);
        $impl->setIsForced(true);
        return $impl;
    }

    public function getParser(PackageInterface $package)
    {
        return new MapParser(array('.' => '.'));
    }

    /**
     * Update Magento core
     * 
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $initial already installed package version
     * @param PackageInterface             $package package instance
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $package)
    {
        var_dump(__FILE__ . ' on line ' . __LINE__ . ':', $package->getName());
        parent::update($repo, $initial, $package);

        $this->prepareMagento($package);
    }

    protected function prepareMagento(PackageInterface $package)
    {
        if (!$this->skipPackageDeployment) {
            $this->copyMagePhp($package);
            $this->setMagentoPermissions();
        }
    }

    /**
     * Copy Mage.php as it contains file system operations that don't allow this file to be symlinked
     * 
     * @param PackageInterface $package 
     */
    protected function copyMagePhp(PackageInterface $package)
    {
        $appFolder = DIRECTORY_SEPARATOR . 'app';
        $sourceDir = $this->getSourceDir($package) . $appFolder;
        $targetDir = $this->getTargetDir() . $appFolder;
        $strategy = new \MagentoHackathon\Composer\Magento\Deploystrategy\Copy($sourceDir, $targetDir);
        $strategy->setMappings(array(
            'app/Mage.php' => 'app/Mage.php'
        ));
        $strategy->deploy();
        echo "copied Mage.php" . PHP_EOL;
    }

    /**
     * some directories have to be writable for the server
     */
    protected function setMagentoPermissions()
    {
        $writableDirs = array(
            'media',
            'var'
        );
        foreach ($writableDirs as $dir) {
            $this->setPermissions($this->getTargetDir() . DIRECTORY_SEPARATOR. $dir, 0777, 0666);
            echo "set permissions for {$this->getTargetDir()}/$dir" . PHP_EOL;
        }
    }

    /**
     * set permissions recursively
     *
     * @param string $path     Path to set permissions for
     * @param int    $dirmode  Permissions to be set for directories
     * @param int    $filemode Permissions to be set for files
     */
    protected function setPermissions($path, $dirmode, $filemode)
    {
        if (is_dir($path) ) {
            if (!chmod($path, $dirmode)) {
                $filemode_str=decoct($filemode);
                throw new Exception(
                    sprintf(
                        'Failed to set permissions "%s" for directory "%s"',
                        decoct($dirmode),
                        $path
                    )
                );
            }
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if($file != '.' && $file != '..') {  // skip self and parent pointing directories
                    $fullpath = $path.'/'.$file;
                    $this->setPermissions($path . '/' . $file, $dirmode, $filemode);
                }
            }
            closedir($dh);
        } else {
            if (is_link($path)) {
                $this->setPermissions(readlink($path), $dirmode, $filemode);
            }
            if (false == !chmod($path, $filemode)) {
                $filemode_str=decoct($filemode);
                throw new Exception(
                    sprintf(
                        'Failed to set permissions "%s" for file "%s"',
                        decoct($filemode),
                        $path
                    )
                );
            }
        }
    }
}
