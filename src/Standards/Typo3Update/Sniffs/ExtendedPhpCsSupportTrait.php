<?php
namespace Typo3Update\Sniffs;

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

use PHP_CodeSniffer_File as PhpcsFile;
use PHP_CodeSniffer_Tokens as Tokens;

/**
 * Provide common uses for all sniffs.
 */
trait ExtendedPhpCsSupportTrait
{
    /**
     * Check whether current stackPtr is a function call.
     *
     * Code taken from PEAR_Sniffs_Functions_FunctionCallSignatureSniff for reuse.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     *
     * @return bool
     */
    protected function isFunctionCall(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Find the next non-empty token.
        $openBracket = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);

        if ($tokens[$openBracket]['code'] !== T_OPEN_PARENTHESIS) {
            // Not a function call.
            return false;
        }

        if (isset($tokens[$openBracket]['parenthesis_closer']) === false) {
            // Not a function call.
            return false;
        }

        // Find the previous non-empty token.
        $search   = Tokens::$emptyTokens;
        $search[] = T_BITWISE_AND;
        $previous = $phpcsFile->findPrevious($search, ($stackPtr - 1), null, true);
        if ($tokens[$previous]['code'] === T_FUNCTION) {
            // It's a function definition, not a function call.
            return false;
        }

        return true;
    }

    /**
     * Returns all parameters for function call as values.
     * Quotes are removed from strings.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     *
     * @return array<string>
     */
    protected function getFunctionCallParameters(PhpCsFile $phpcsFile, $stackPtr)
    {
        $start = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr) + 1;
        $parameters = explode(',', $phpcsFile->getTokensAsString(
            $start,
            $phpcsFile->findNext(T_CLOSE_PARENTHESIS, $stackPtr) - $start
        ));

        return array_map([$this, 'getStringContent'], $parameters);
    }

    /**
     * Remove special chars like quotes from string.
     *
     * @param string $string
     * @return string
     */
    public function getStringContent($string)
    {
        return trim($string, " \t\n\r\0\x0B'\"");
    }
}
