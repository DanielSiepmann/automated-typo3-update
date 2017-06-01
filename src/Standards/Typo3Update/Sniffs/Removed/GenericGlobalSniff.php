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

class GenericGlobalSniff extends AbstractGenericUsage
{
    use ExtendedPhpCsSupportTrait;

    public function register()
    {
        return [T_VARIABLE];
    }

    /**
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     * @return array
     */
    protected function findRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        if ($this->isGlobalVariable($phpcsFile, $stackPtr) === false) {
            return [];
        }

        $variableName = substr($phpcsFile->getTokens()[$stackPtr]['content'], 1);
        if ($variableName === 'GLOBALS') {
            $variableName = trim(
                $phpcsFile->getTokens()[$phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, $stackPtr)]['content'],
                '\'"'
            );
        }

        if ($this->configured->isRemoved($variableName)) {
            return [$this->configured->getRemoved($variableName)];
        }

        return [];
    }

    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedGlobalConfigFiles();
    }
}
