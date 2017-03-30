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
use PHP_CodeSniffer_Tokens as PhpCsTokens;

/**
 * Analyses feature 6991.
 *
 * @see https://docs.typo3.org/typo3cms/extensions/core/Changelog/7.6/Feature-69916-PSR-7-basedRoutingForBackendAJAXRequests.html
 */
class Typo3Update_Sniffs_Deprecated_AjaxRegistrationSniff implements PhpCsSniff
{
    /**
     * Defines files to check.
     * As soon as PHP_CS 3 is used, define them in ruleset.xml.
     *
     * @var array<string>
     */
    public $filenamesToCheck = [
        'ext_tables.php',
        'ext_localconf.php',
    ];

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return PhpCsTokens::$stringTokens;
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
        if (in_array(basename($phpcsFile->getFilename()), $this->filenamesToCheck) === false) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['content'] !== "'AJAX'") {
            return;
        }
        $equalSign = $phpcsFile->findNext(T_EQUAL, $stackPtr, null, false, null, true);
        if ($equalSign === false) {
            return;
        }
        $tokenToCheck = $phpcsFile->findNext(T_WHITESPACE, $equalSign + 1, null, true, null, true);
        if ($tokenToCheck === false) {
            return;
        }
        $token = $tokens[$tokenToCheck];
        if ($token['code'] !== T_CONSTANT_ENCAPSED_STRING) {
            return;
        }

        $phpcsFile->addWarning(
            'Defining AJAX using %s is no longer supported with a single String like %s.'
            . ' Since TYPO3 7.6, use PSR-7-based Routing for Backend AJAX Requests.'
            . ' See: %s',
            $tokenToCheck,
            '',
            [
                "\$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX'][\$ajaxID]",
                $token['content'],
                'https://docs.typo3.org/typo3cms/extensions/core/Changelog/7.6/Feature-69916-PSR-7-basedRoutingForBackendAJAXRequests.html'
            ]
        );
    }
}
