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
use PHP_CodeSniffer_Tokens as Tokens;
use Typo3Update\Sniffs\Classname\AbstractClassnameChecker;

/**
 * Detect and migrate old legacy classname instantiations using objectmanager create and get.
 */
class Typo3Update_Sniffs_Classname_InstantiationWithObjectManagerSniff extends AbstractClassnameChecker
{
    use \Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return Tokens::$functionNameTokens;
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
        if (!$this->isFunctionCall($phpcsFile, $stackPtr)) {
            return;
        }
        $tokens = $phpcsFile->getTokens();

        $functionName = $tokens[$stackPtr]['content'];
        if (!in_array($functionName, ['get', 'create'])) {
            return;
        }

        $classnamePosition = $phpcsFile->findNext(
            T_CONSTANT_ENCAPSED_STRING,
            $stackPtr,
            $phpcsFile->findNext(T_CLOSE_PARENTHESIS, $stackPtr)
        );
        if ($classnamePosition === false) {
            return;
        }

        if ($functionName === 'create') {
            $phpcsFile->addWarning(
                'The "create" method of ObjectManager is no longer supported, please migrate to "get".',
                $stackPtr,
                'mightBeDeprecatedMethod',
                ['create']
            );
        }

        $classname = $tokens[$classnamePosition]['content'];
        $this->processFeatures($phpcsFile, $classnamePosition, $classname);
    }
}
