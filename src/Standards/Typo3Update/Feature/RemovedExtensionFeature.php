<?php
namespace Typo3Update\Feature;

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
use Typo3Update\Options;

class RemovedExtensionFeature extends AbstractYamlRemovedUsage
{
    public function process(PhpCsFile $phpcsFile, $classnamePosition, $classname)
    {
        $extname = $this->getExtnameFromClassname($classname);
        if ($extname === '' || $this->configured->isRemoved($extname) === false) {
            return;
        }

        $this->addWarning(
            $phpcsFile,
            $classnamePosition,
            $this->configured->getRemoved($extname)
        );
    }

    protected function getExtnameFromClassname($classname)
    {
        $classname = ltrim($classname, '\\');
        $classnameParts = array_filter(preg_split('/\\\\|_/', $classname));
        $classnameParts = array_values($classnameParts); // To reset key numbers of array.
        $extname = '';

        if (count($classnameParts) <= 2) {
            return '';
        }

        $extname = $classnameParts[1];
        if (stripos($classname, 'TYPO3\CMS') === 0) {
            $extname = $classnameParts[2];
        }

        return strtolower($extname);
    }

    protected function prepareStructure(array $typo3Versions)
    {
        $newStructure = [];
        foreach ($typo3Versions as $typo3Version => $removals) {
            foreach ($removals as $removed => $config) {
                $config['name'] = $removed;
                $config['identifier'] = 'RemovedExtension.' . str_replace('\\', '_', ltrim($removed, '\\'));
                $config['versionRemoved'] = $typo3Version;
                $config['oldUsage'] = $removed;

                $newStructure[$removed] = $config;
            }
        }

        return $newStructure;
    }

    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedExtensionConfigFiles();
    }
}
