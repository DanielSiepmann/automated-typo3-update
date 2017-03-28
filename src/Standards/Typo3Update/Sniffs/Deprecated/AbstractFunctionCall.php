<?php
namespace Typo3Update\Sniffs\Deprecated;

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
use PHP_CodeSniffer_Tokens as Tokens;

/**
 *
 */
abstract class AbstractFunctionCall implements PhpCsSniff
{
    use \Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;

    abstract protected function getFunctionNames();
    abstract protected function getOldFunctionCall($functionName);
    abstract protected function getNewFunctionCall($functionName);
    abstract protected function getDocsUrl($functionName);

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
     * This is the default implementation, as most of the time next T_STRING is
     * the class name. This way only the register method has to be registered
     * in default cases.
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
        $token = $tokens[$stackPtr];

        if (in_array($token['content'], $this->getFunctionNames()) === false) {
            return;
        }

        $this->addWarning($phpcsFile, $stackPtr);
    }

    protected function addWarning(PhpCsFile $phpcsFile, $tokenPosition)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$tokenPosition];
        $functionCall = $token['content'];

        $phpcsFile->addWarning(
            'Legacy function calls are not allowed; found %s. %s. See: %s',
            $tokenPosition,
            'legacyFunctioncall',
            [
                $this->getOldFunctionCall($functionCall),
                $this->getNewFunctionCall($functionCall),
                $this->getDocsUrl($functionCall),
            ]
        );
    }
}
