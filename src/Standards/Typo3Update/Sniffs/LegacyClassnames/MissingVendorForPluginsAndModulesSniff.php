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
use PHP_CodeSniffer_Tokens as Tokens;

/**
 * Detect whether vendor is missing for plugins and modules registrations and configurations.
 */
class Typo3Update_Sniffs_LegacyClassnames_MissingVendorForPluginsAndModulesSniff implements PhpCsSniff
{
    use \Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;
    use \Typo3Update\Sniffs\OptionsAccessTrait;

    /**
     * Original token content for reuse accross methods.
     * @var string
     */
    protected $originalTokenContent = '';

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
        $functionsToHandle = ['registerPlugin', 'configurePlugin', 'registerModule', 'configureModule'];
        if (!$this->isFunctionCall($phpcsFile, $stackPtr)) {
            return;
        }
        $tokens = $phpcsFile->getTokens();

        if (!in_array($tokens[$stackPtr]['content'], $functionsToHandle)) {
            return;
        }

        $firstArgument = $phpcsFile->findNext([T_WHITESPACE, T_OPEN_PARENTHESIS], $stackPtr + 1, null, true);
        if (! $this->isVendorMissing($phpcsFile, $firstArgument)) {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            'No vendor is given, that will break TYPO3 handling for namespaced classes.'
                . ' Add vendor before Extensionkey like: "Vendor." . $_EXTKEY',
            $firstArgument,
            'missingVendor'
        );

        if ($fix === true) {
            $phpcsFile->fixer->replaceToken(
                $firstArgument,
                "'{$this->getVendor()}.' . " . $tokens[$firstArgument]['content']
            );
        }
    }

    /**
     * Checks whether vendor is missing in given argument.
     *
     * @param PhpCsFile $phpcsFile
     * @param int|bool $argumentStart
     *
     * @return bool
     */
    protected function isVendorMissing(PhpCsFile $phpcsFile, $argumentStart)
    {
        if ($argumentStart === false) {
            return false;
        }

        $argumentEnd = $phpcsFile->findNext(T_COMMA, $argumentStart);
        if ($argumentEnd === false) {
            return false;
        }

        $stringConcats = array_filter(
            array_slice($phpcsFile->getTokens(), $argumentStart, $argumentEnd - $argumentStart),
            function (array $token) {
                return $token['code'] === 'PHPCS_T_STRING_CONCAT';
            }
        );

        return count($stringConcats) === 0;
    }
}
