#!/usr/bin/env php
<?php
require __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Typo3Update\CommandLine\Commands\Fluid\ReplaceNameSpaceImport;

call_user_func(function () {
    $application = new Application();

    $command = new ReplaceNameSpaceImport();
    $application->add($command);
    $application->setDefaultCommand($command->getName(), true);

    $application->run();
});
