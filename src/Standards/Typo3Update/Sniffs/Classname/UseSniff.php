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
use PHP_CodeSniffer_Sniff as PhpCsSniff;
use Typo3Update\Feature\FeaturesSupport;

class Typo3Update_Sniffs_Classname_UseSniff implements PhpCsSniff
{
    use FeaturesSupport;

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [T_USE];
    }

    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        $start = $phpcsFile->findNext(T_STRING, $stackPtr);
        if ($start === false) {
            return;
        }

        $end = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $start, null, true, null, true);
        if ($end === false) {
            return;
        }

        $classname = $phpcsFile->getTokensAsString($start, $end - $start);
        $this->processFeatures($phpcsFile, $start, $classname);
    }
}
