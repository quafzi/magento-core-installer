# Magento Core Installer for Composer

Want to kick-off a new Magento project? What about dropping all the Magento core components from your project's
repository?

## Usage

First of all, you should use
(magento-composer-installer)[https://github.com/magento-hackathon/magento-composer-installer]
to uncouple your extensions.

Now just add Magento itself as a new dependency using this Magento Core Installer. Therefor you need to generate a
modman file (or another kind of mapping) for your Magento version and to provide a `composer.json` depending on Magento
Core Installer.

Example:

    {
      "name": "magento/core",
      "type": "magento-core",
      "description": "Magento Core",
      "require": {
        "quafzi/magento-core-installer": "dev-master"
      },
      "repositories": [
        {
          "type": "vcs",
          "url": "https://github.com/quafzi/magento-core-installer.git"
        }
      ]
    }

## How to generate a modman file for a whole Magento version?

Run `mage_modman_generator.sh` (which is part of this repository) in your Magento root folder.
