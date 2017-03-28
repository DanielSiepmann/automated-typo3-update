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
use Symfony\Component\Yaml\Yaml;

/**
 * Sniff that handles all calls to deprecated functions.
 *
 * A single array defines the deprecations, see $deprecatedFunctions.
 */
class Typo3Update_Sniffs_Deprecated_GenericFunctionCallSniff implements PhpCsSniff
{
    use \Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;
    use \Typo3Update\Sniffs\OptionsAccessTrait;

    /**
     * Configuration to define deprecated functions.
     *
     * Keys have to match the function name.
     *
     * TODO: Multiple files allowed, using glob ... to allow splitting per ext (extbase, fluid, ...) and TYPO3 Version 7.1, 7.0, ...
     * TODO: Build necessary structure from that files, to make it more independent ... ?!
     *
     * @var array
     */
    protected static $deprecatedFunctions = [];

    public function __construct()
    {
        if (static::$deprecatedFunctions === []) {
            foreach ($this->getDeprecatedFunctionConfigFiles() as $file) {
                static::$deprecatedFunctions = array_merge(
                    static::$deprecatedFunctions,
                    Yaml::parse(file_get_contents((string) $file))
                );
            }
        }
    }

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

        // TODO: Check if function is static and whether last class name part matches.
        // TODO: Add new property to array "last class name part" and use for check if exists.
        // TODO: How to handle methods? They are not static, are behind a variable or something else ...

        // E.g.: getUniqueFields
        // E.g.: mail

        $this->addWarning($phpcsFile, $stackPtr);
    }

    /**
     * Add warning for the given token position.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $tokenPosition
     *
     * @return void
     */
    protected function addWarning(PhpCsFile $phpcsFile, $tokenPosition)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$tokenPosition];
        $functionCall = $token['content'];

        $phpcsFile->addWarning(
            'Legacy function calls are not allowed; found %s. %s. See: %s',
            $tokenPosition,
            $functionCall,
            [
                $this->getOldFunctionCall($functionCall),
                $this->getNewFunctionCall($functionCall),
                $this->getDocsUrl($functionCall),
            ]
        );
    }

    /**
     * Provide all function names that are deprecated and should be handled by
     * the Sniff.
     *
     * @return array
     */
    protected function getFunctionNames()
    {
        return array_keys(static::$deprecatedFunctions);
    }

    /**
     * The original function call, to allow user to check matches.
     *
     * As we match the function name, that can be provided by multiple classes,
     * you should provide an example, so users can check that this is the
     * legacy one.
     *
     * @return string
     */
    protected function getOldFunctionCall($functionName)
    {
        return static::$deprecatedFunctions[$functionName]['oldFunctionCall'];
    }

    /**
     * The new function call, or information how to migrate.
     *
     * To provide feedback for user to ease migration.
     *
     * @return string
     */
    protected function getNewFunctionCall($functionName)
    {
        $newCall = static::$deprecatedFunctions[$functionName]['newFunctionCall'];
        if ($newCall !== null) {
            return $newCall;
        }
        return 'There is no replacement, just remove call';
    }

    /**
     * Allow user to lookup the official docs related to this deprecation / breaking change.
     *
     * @return string
     */
    protected function getDocsUrl($functionName)
    {
        return static::$deprecatedFunctions[$functionName]['docsUrl'];
    }
}
