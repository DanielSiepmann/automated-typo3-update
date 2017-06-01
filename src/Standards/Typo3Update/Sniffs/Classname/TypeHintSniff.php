<?php

namespace Typo3Update\Sniffs\Classname;

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

class TypeHintSniff extends AbstractClassnameChecker
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

            $position = $phpcsFile->findPrevious([
                T_OPEN_PARENTHESIS, T_COMMA
            ], $parameter['token'] - 2, $stackPtr, false, null, true);
            if ($position === false) {
                continue;
            }

            $position = $phpcsFile->findNext(T_STRING, $position);
            if ($position === false) {
                continue;
            }

            $this->processFeatures($phpcsFile, $position, $parameter['type_hint']);
        }
    }
}
