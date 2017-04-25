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
use Typo3Update\Sniffs\Classname\AbstractClassnameChecker;

/**
 * Migrate Typehints in function / method definitions.
 */
class Typo3Update_Sniffs_Classname_TypehintSniff extends AbstractClassnameChecker
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PhpCsFile $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        $params = $phpcsFile->getMethodParameters($stackPtr);
        foreach ($params as $parameter) {
            if ($parameter['type_hint'] === '') {
                continue;
            }

            $position = $phpcsFile->findPrevious(T_STRING, $parameter['token'], $stackPtr, false, null, true);
            if ($position === false) {
                continue;
            }
            $this->processFeatures($phpcsFile, $position, $parameter['type_hint']);
        }
    }
}
