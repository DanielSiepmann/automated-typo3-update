<?php
namespace Typo3Update\Sniffs\Removed;

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

use PHP_CodeSniffer_File as PhpCsFile;
use PHP_CodeSniffer_Sniff as PhpCsSniff;
use Typo3Update\RemovedByYamlConfiguration;

/**
 * Contains common functionality for removed code like constants or functions.
 *
 * Removed parts are configured using YAML-Files, for examples see
 * src/Standards/Typo3Update/Configuration/Removed/Constants/7.0.yaml Also
 * check out the configuration options in Readme.rst.
 */
abstract class AbstractGenericUsage implements PhpCsSniff
{
    protected $configured;

    public function __construct()
    {
        $this->configured = new RemovedByYamlConfiguration(
            $this->getRemovedConfigFiles(),
            \Closure::fromCallable([$this, 'prepareStructure'])
        );
    }

    /**
     * Prepares structure from config for later usage.
     *
     * @param array $typo3Versions
     * @return array
     */
    abstract protected function prepareStructure(array $typo3Versions);

    /**
     * Return file names containing removed configurations.
     *
     * @return array<string>
     */
    abstract protected function getRemovedConfigFiles();

    abstract protected function findRemoved(PhpCsFile $phpcsFile, $stackPtr);

    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        $removed = $this->findRemoved($phpcsFile, $stackPtr);
        if ($removed === []) {
            return;
        }

        $this->addMessage($removed);
    }

    protected function addMessage(array $removed)
    {
        foreach ($removed as $removed) {
            $phpcsFile->addWarning(
                'Calls to removed code are not allowed; found %s. Removed in %s. %s. See: %s',
                $tokenPosition,
                $removed['identifier'],
                [
                    $removed['oldUsage'],
                    $removed['versionRemoved'],
                    $this->getReplacement($removed),
                    $removed['docsUrl'],
                ]
            );
        }
    }

    /**
     * The new call, or information how to migrate.
     *
     * To provide feedback for user to ease migration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getReplacement(array $config)
    {
        $newCall = $config['replacement'];
        if ($newCall !== null) {
            return $newCall;
        }
        return 'There is no replacement, just remove call';
    }
}
