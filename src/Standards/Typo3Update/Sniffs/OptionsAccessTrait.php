<?php
namespace Typo3Update\Sniffs;

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

use PHP_CodeSniffer as PhpCs;

/**
 * Wrapper to retrieve options from PhpCs with defaults.
 */
trait OptionsAccessTrait
{
    /**
     * Returns the configured vendor, e.g. to generate new namespaces.
     *
     * @return string
     */
    public function getVendor()
    {
        $vendor = PhpCs::getConfigData('vendor');
        if (!$vendor) {
            $vendor = 'YourCompany';
        }
        return trim($vendor, '\\/');
    }

    /**
     * Returns the configured file path containing the mappings for classes, interfaced and traits.
     *
     * @return string
     */
    public function getMappingFile()
    {
        $mappingFile = PhpCs::getConfigData('mappingFile');
        if (!$mappingFile) {
            $mappingFile = __DIR__ . '/../../../../LegacyClassnames.php';
        }
        return $mappingFile;
    }

    /**
     * Returns an array of absolute file names containing removed function configurations.
     *
     * @return \Generator
     */
    public function getRemovedFunctionConfigFiles()
    {
        $configFiles = PhpCs::getConfigData('removedFunctionConfigFiles');
        if (!$configFiles) {
            $configFiles = __DIR__ . '/../Configuration/Removed/Functions/*.yaml';
        }

        foreach ((new \GlobIterator($configFiles)) as $file) {
            yield (string) $file;
        }
    }

    /**
     * Returns an array of absolute file names containing removed constant configurations.
     *
     * @return \Generator
     */
    public function getRemovedConstantConfigFiles()
    {
        $configFiles = PhpCs::getConfigData('removedConstantConfigFiles');
        if (!$configFiles) {
            $configFiles = __DIR__ . '/../Configuration/Removed/Constants/*.yaml';
        }

        foreach ((new \GlobIterator($configFiles)) as $file) {
            yield (string) $file;
        }
    }
}
