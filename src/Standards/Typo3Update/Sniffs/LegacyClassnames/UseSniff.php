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
use Typo3Update\Sniffs\LegacyClassnames\AbstractClassnameChecker;

/**
 * Detect and migrate use statements with legacy classnames..
 *
 * According to PSR-2, only one class per use statement is expected.
 */
class Typo3Update_Sniffs_LegacyClassnames_UseSniff extends AbstractClassnameChecker
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [T_USE];
    }

    /**
     * Overwrite to remove prefix.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     * @param bool $forceEmptyPrefix Defines whether '\\' prefix should be checked or always be left out.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function replaceLegacyClassname(
        PhpCsFile $phpcsFile,
        $classnamePosition,
        $classname,
        $forceEmptyPrefix = true
    ) {
        return parent::replaceLegacyClassname($phpcsFile, $classnamePosition, $classname, $forceEmptyPrefix);
    }
}
