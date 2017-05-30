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
use PHP_CodeSniffer\Sniffs\Sniff as PhpCsSniff;
use PHP_CodeSniffer\Util\Tokens as PhpCsTokens;
use Typo3Update\Feature\FeaturesSupport;

class StringSniff implements PhpCsSniff
{
    use FeaturesSupport;

    /**
     * @return array<int>
     */
    public function register()
    {
        return PhpCsTokens::$stringTokens;
    }

    /**
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     */
    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        $token = $phpcsFile->getTokens()[$stackPtr]['content'];
        // Special chars like ":" and "&" are used in configuration directives.
        $classnames = array_filter(preg_split('/\s+|:|->|&/', substr($token, 1, -1)));

        foreach ($classnames as $classname) {
            $this->processFeatures($phpcsFile, $stackPtr, $classname);
        }
    }
}
