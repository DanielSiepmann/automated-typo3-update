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
use PHP_CodeSniffer\Sniffs\Sniff as PhpCsSniff;
use Typo3Update\AbstractYamlRemovedUsage as BaseAbstractYamlRemovedUsage;

abstract class AbstractGenericUsage extends BaseAbstractYamlRemovedUsage implements PhpCsSniff
{
    /**
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     * @return array
     */
    abstract protected function findRemoved(PhpCsFile $phpcsFile, $stackPtr);

    /**
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     */
    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        foreach ($this->findRemoved($phpcsFile, $stackPtr) as $removed) {
            $this->addWarning($phpcsFile, $stackPtr, $removed);
        }
    }
}
