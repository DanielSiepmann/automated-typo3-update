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

use PHP_CodeSniffer\Files\File as PhpCsFile;
use Typo3Update\Options;
use Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;

class GenericSignalSniff extends AbstractGenericPhpUsage
{
    use ExtendedPhpCsSupportTrait;

    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedSignalConfigFiles();
    }

    protected function findRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        if (!$this->isFunctionCall($phpcsFile, $stackPtr) || $phpcsFile->getTokens()[$stackPtr]['content'] !== 'connect') {
            return [];
        }

        $parameters = $this->getFunctionCallParameters($phpcsFile, $stackPtr);
        if (count($parameters) < 4) {
            return [];
        }

        $lookup = $this->getClass($parameters[0]) . '::' . $parameters[1];

        if ($this->configured->isRemoved($lookup) === false) {
            return [];
        }

        return [$this->configured->getRemoved($lookup)];
    }

    /**
     * Returns same formatted class representation for incoming strings.
     *
     * @param string $string
     * @return string
     */
    protected function getClass($string)
    {
        $search = [
            '::class',
            '\\\\',
        ];
        $replace = [
            '',
            '\\',
        ];

        $string = str_replace($search, $replace, $string);

        if ($string[0] !== '\\') {
            $string = '\\' . $string;
        }

        return $string;
    }

    protected function getOldUsage(array $config)
    {
        return $config['fqcn'] . '::' . $config['name'];
    }
}
