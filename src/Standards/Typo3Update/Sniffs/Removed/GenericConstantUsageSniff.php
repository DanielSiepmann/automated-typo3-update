<?php

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
use Typo3Update\Sniffs\Removed\AbstractGenericUsage;

/**
 * Sniff that handles all calls to removed constants.
 *
 * Removed constants are configured using YAML-Files, for examples see src/Standards/Typo3Update/Configuration/Removed/Constants/7.0.yaml
 * Also check out the configuration options in Readme.rst.
 */
class Typo3Update_Sniffs_Removed_GenericConstantUsageSniff extends AbstractGenericUsage
{
    /**
     * Return file names containing removed configurations.
     *
     * @return array<string>
     */
    protected function getRemovedConfigFiles()
    {
        return $this->getRemovedConstantConfigFiles();
    }

    /**
     * Prepares structure from config for later usage.
     *
     * @param array $typo3Versions
     * @return array
     */
    // protected function prepareStructure(array $typo3Versions)
    // {
    //     $newStructure = [];

    //     foreach ($typo3Versions as $typo3Version => $constants) {
    //         foreach ($constants as $constant => $config) {
    //             $split = explode('::', $constant);

    //             $newStructure[$constant] = $config;

    //             $newStructure[$constant]['static'] = strpos($constant, '::') !== false;
    //             $newStructure[$constant]['fqcn'] = null;
    //             $newStructure[$constant]['class'] = null;
    //             $newStructure[$constant]['constant'] = $constant;
    //             $newStructure[$constant]['version_removed'] = $typo3Version;
    //             // TODO: Handle constants of classes
    //         };
    //     }

    //     return $newStructure;
    // }

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [T_STRING];
    }

    /**
     * The original constant call, to allow user to check matches.
     *
     * As we match the constant name, that can be provided by multiple classes,
     * you should provide an example, so users can check that this is the
     * legacy one.
     *
     * @param array $config The converted structure for a single constant.
     *
     * @return string
     */
    protected function getOldUsage(array $config)
    {
        $old = $config['name'];
        if ($config['static']) {
            $old = $config['fqcn'] . '::' . $config['name'];
        }

        return 'constant ' . $old;
    }
}
