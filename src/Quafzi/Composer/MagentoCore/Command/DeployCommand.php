<?php

/**
 * Composer Magento Installer
 */

namespace Quafzi\Composer\MagentoCore\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Downloader\VcsDownloader;

/**
 * @author Tiago Ribeiro <tiago.ribeiro@seegno.com>
 * @author Rui Marinho <rui.marinho@seegno.com>
 */
class DeployCommand extends \Composer\Command\Command
{
    protected function configure()
    {
        $this
            ->setName('magento-core-deploy')
            ->setDescription('Deploy Magento Core loaded via composer.json')
            ->setDefinition(array(
        ))
            ->setHelp(<<<EOT
This command deploys Magento Core

EOT
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // init repos
        $composer = $this->getComposer();
        $installedRepo = $composer->getRepositoryManager()->getLocalRepository();

        $dm = $composer->getDownloadManager();
        $im = $composer->getInstallationManager();

        /*
         * @var $moduleInstaller Quafzi\Composer\MagentoCore\Installer
         */
        $moduleInstaller = $im->getInstaller("magento-core");


        foreach ($installedRepo->getPackages() as $package) {

            if ($input->getOption('verbose')) {
                $output->writeln( $package->getName() );
                $output->writeln( $package->getType() );
            }

            if( $package->getType() != "magento-core" ){
                continue;
            }
            if ($input->getOption('verbose')) {
                $output->writeln("package {$package->getName()} recognized");
            }

            $strategy = $moduleInstaller->getDeployStrategy($package);
            if ($input->getOption('verbose')) {
                $output->writeln("used " . get_class($strategy) . " as deploy strategy");
            }
            $strategy->setMappings($moduleInstaller->getParser($package)->getMappings());

            $strategy->deploy();
        }


        return;
    }
}
