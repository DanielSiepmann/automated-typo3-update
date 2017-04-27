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

abstract class AbstractGenericPhpUsage extends AbstractGenericUsage
{
    public function register()
    {
        return [T_STRING];
    }

    protected function prepareStructure(array $typo3Versions)
    {
        $newStructure = [];

        foreach ($typo3Versions as $typo3Version => $removals) {
            foreach ($removals as $removed => $config) {
                $newStructure[$removed] = $config;

                $newStructure[$removed]['fqcn'] = null;
                $newStructure[$removed]['class'] = null;
                $newStructure[$removed]['versionRemoved'] = $typo3Version;

                $this->handleStatic($removed, $newStructure[$removed]);

                $newStructure[$removed]['oldUsage'] = $this->getOldUsage($newStructure[$removed]);
                $newStructure[$removed]['identifier'] = $this->getIdentifier($newStructure[$removed]);
            };
        }

        return $newStructure;
    }

    protected function findRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $staticPosition = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true, null, true);

        $name = $tokens[$stackPtr]['content'];
        $isStatic = false;
        $class = false;

        if ($staticPosition !== false) {
            $isStatic = $tokens[$staticPosition]['code'] === T_DOUBLE_COLON;
        }

        if ($isStatic) {
            $class = $phpcsFile->findPrevious(T_STRING, $staticPosition, null, false, null, true);
            if ($class !== false) {
                $class = $tokens[$class]['content'];
            }
        }

        return $this->getMatchingRemoved($name, $class, $isStatic);
    }

    /**
     * Returns all matching removed functions for given arguments.
     *
     * @param string $name
     * @param string $className The last part of the class name, splitted by namespaces.
     * @param bool $isStatic
     *
     * @return array
     */
    protected function getMatchingRemoved($name, $className, $isStatic)
    {
        // We will not match any static calls, without the class name, at least for now.
        if ($isStatic === true && $className === false) {
            return [];
        }

        return array_filter(
            $this->configured->getAllRemoved(),
            function ($config) use ($name, $isStatic, $className) {
                return $name === $config['name']
                    && $isStatic === $config['static']
                    && (
                        $className === $config['class']
                        || $className === false
                    )
                ;
            }
        );
    }

    protected function handleStatic($identifier, array &$config)
    {
        $split = preg_split('/::|->/', $identifier);

        $config['name'] = $split[0];
        $config['static'] = strpos($identifier, '::') !== false;

        if (isset($split[1])) {
            $config['fqcn'] = $split[0];
            $config['class'] = array_slice(explode('\\', $config['fqcn']), -1)[0];
            $config['name'] = $split[1];
        }
    }

    protected function getIdentifier(array $config)
    {
        $name = $config['name'];
        if ($config['class']) {
            $name = $config['class'] . '.' . $name;
        }

        return $name;
    }

    abstract protected function getOldUsage(array $config);
}
