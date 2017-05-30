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

use PHP_CodeSniffer\Files\File as PhpCsFile;
use Typo3Update\Options;

class RemovedClassFeature extends AbstractYamlRemovedUsage
{
    public function process(PhpCsFile $phpcsFile, $classnamePosition, $classname)
    {
        if ($this->configured->isRemoved($classname) === false) {
            return;
        }
        $this->addWarning(
            $phpcsFile,
            $classnamePosition,
            $this->configured->getRemoved($classname)
        );
    }

    protected function prepareStructure(array $typo3Versions)
    {
        $newStructure = [];
        foreach ($typo3Versions as $typo3Version => $removals) {
            foreach ($removals as $removed => $config) {
                $config['name'] = $removed;
                $config['identifier'] = 'RemovedClass:' . str_replace('\\', '_', ltrim($removed, '\\'));
                $config['versionRemoved'] = $typo3Version;
                $config['oldUsage'] = $removed;

                $newStructure[$removed] = $config;
            }
        }

        return $newStructure;
    }

    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedClassConfigFiles();
    }
}
