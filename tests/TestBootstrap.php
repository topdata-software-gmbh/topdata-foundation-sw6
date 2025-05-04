<?php declare(strict_types=1);

use Shopware\Core\TestBootstrapper;

$loader = (new TestBootstrapper())
    ->addCallingPlugin()
    ->addActivePlugins('TopdataFoundationSW6')
    ->setForceInstallPlugins(true) // ensure that your plugin is installed and active even the test database was already build beforehand.
    ->bootstrap()
    ->getClassLoader();

$loader->addPsr4('Topdata\\TopdataFoundationSW6\\Tests\\', __DIR__);
