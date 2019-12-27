# Magento Demo Login Module

A tiny magento module which allows you to login into the magento backend without giving credentials out. The most common use-case is probably a demo installation for a module or a theme.  

![Screenshot](doc/login.jpeg?raw=true)

 ## Compatibility
 * Magento >= 2.3.0 (not tested on any older version)

## Installation

### Installation via composer:

    composer require hampe/demo-login
    php bin/magento module:enable Hampe_DemoLogin
    php bin/magento setup:upgrade 

### Installation via Copy 

Copy all the files under src/ into the newly created directory app/code/Hampe/DemoLogin/ in the Magento 2 root.

    php bin/magento module:enable Hampe_DemoLogin
    php bin/magento setup:upgrade 
 
## Uninstallation

    bin/magento module:uninstall Hampe_DemoLogin.

Remove all extension files from app/code/Hampe/DemoLogin/ or use Composer to remove the extension if you have installed it with Composer

## Configuration

The configuration can be found under `Stores > Configuration > Admin > Demo Login` 

## Licence

See the [LICENSE](LICENSE) file for license info (it's the MIT license).