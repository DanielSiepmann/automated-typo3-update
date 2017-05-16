<?php
namespace Typo3Update\CommandLine\Commands\Fluid;

/*
 * Copyright (C) 2017  Daniel Siepmann <coding@daniel-siepmann.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 *
 */
class ReplaceNameSpaceImport extends Command
{
    protected $input;
    protected $output;

    protected function configure()
    {
        $this->setName('Typo3Update:generateReplaceNamespaceImports')
             ->setDescription('Will dump command to replace Namespace imports in Fluid.')
             ->setHelp('Replaces =Tx_ExtName_ViewHelpers with =\Vendor\ExtName\ViewHelpers. SQL is provided as some might have fluid in their database.')

             ->addArgument('documentRoot', InputArgument::REQUIRED, 'The document root containing TYPO3 installation.')
             ->addArgument('vendor', InputArgument::REQUIRED, 'Namespace vendor to use.')

             ->addOption('hide-cli', null, InputOption::VALUE_OPTIONAL, 'Do not display cli command output.', false)
             ->addOption('hide-sql', null, InputOption::VALUE_OPTIONAL, 'Do not display sql query output.', false)
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        foreach ($this->getExtensionKeys($input->getArgument('documentRoot')) as $extKey) {
            $this->migrateExtension($extKey, $input->getArgument('vendor'));
        }
    }

    protected function migrateExtension($extKey, $vendor)
    {
        $extName = implode('', array_map('ucfirst', explode('_', $extKey)));

        if ($this->output->isVerbose()) {
            $this->output->writeln("<info>Processing: $extName</info>");
        }
        if ($this->input->getOption('hide-cli') == false) {
            $this->output->writeln("LC_ALL=C sed -i '' 's#Tx_{$extName}_ViewHelpers#{$vendor}\\\\{$extName}\\\\ViewHelpers#' `ag 'Tx_{$extName}_ViewHelpers' --html -l`;");
        }
        if ($this->input->getOption('hide-sql') == false) {
            $this->output->writeln("UPDATE tt_content SET bodytext = replace(bodytext, 'Tx_{$extName}_ViewHelpers', '{$vendor}\\\\{$extName}\\\\ViewHelpers') WHERE bodytext LIKE '%Tx_{$extName}_ViewHelpers%';");
        }
    }

    protected function getExtensionKeys($documentRoot)
    {
        $finder = new Finder();
        $folders = $finder->directories()
            ->depth('==0')
            ->in($this->getTypo3ConfFolder($documentRoot))
            ;

        foreach ($folders as $folder) {
            yield $folder->getRelativePathname();
        }
    }

    protected function getTypo3ConfFolder($documentRoot)
    {
        $confFolder = $documentRoot . DIRECTORY_SEPARATOR . 'typo3conf' . DIRECTORY_SEPARATOR . 'ext';
        if (is_dir($confFolder)) {
            return $confFolder;
        }

        throw new \Exception('typo3conf/ext folder coult not be found in document root "' . $documentRoot . '"', 1494920027);
    }
}
