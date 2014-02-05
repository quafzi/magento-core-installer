<?php
/**
 * Magento Core Installer for Composer
 */

namespace Quafzi\Composer\MagentoCore;

use MagentoHackathon\Composer\Magento\Installer as MagentoModuleInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\PackageInterface;
use MagentoHackathon\Composer\Magento\Deploystrategy\Copy as CopyStrategy;

/**
 * Composer Magento Core Installer
 */
class Installer extends MagentoModuleInstaller
{
    protected $_deployStrategy = 'copy';

    public function supports($packageType)
    {
        return 'magento-core' === $packageType;
    }

    public function getDeployStrategy(PackageInterface $package, $strategy = null)
    {
        return parent::getDeployStrategy($package, 'copy');
    }

    /**
     * Install Magento core
     * 
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $package package instance
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);

        $this->prepareMagento($package);
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
        parent::update($repo, $initial, $package);

        $this->prepareMagento($package);
    }

    protected function prepareMagento(PackageInterface $package)
    {
        if (!$this->skipPackageDeployment) {
            $this->setMagentoPermissions();
        }
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
            if (!file_exists($this->getTargetDir() . DIRECTORY_SEPARATOR . $dir)) {
                mkdir($this->getTargetDir() . DIRECTORY_SEPARATOR . $dir);
            }
            $this->setPermissions($this->getTargetDir() . DIRECTORY_SEPARATOR. $dir, 0777, 0666);
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
            try {
                $success = chmod($path, $dirmode);
            } catch(\ErrorException $e) {
                $success = false;
            }
            if (false == $success) {
                echo sprintf(
                    'Failed to set permissions "%s" for directory "%s"',
                    decoct($dirmode),
                    $path
                ) . PHP_EOL;
            }
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if($file != '.' && $file != '..') {  // skip self and parent pointing directories
                    $fullpath = $path.'/'.$file;
                    $this->setPermissions($fullpath, $dirmode, $filemode);
                }
            }
            closedir($dh);
        } elseif(is_file($path)) {
            try {
                $success = chmod($path, $filemode);
            } catch(\ErrorException $e) {
                $success = false;
            }
            if (false == $success) {
                echo sprintf(
                    'Failed to set permissions "%s" for file "%s"',
                    decoct($filemode),
                    $path
                ) . PHP_EOL;
            }
        }
    }
}
