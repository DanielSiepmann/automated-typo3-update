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

/**
 * This sniff detects old, legacy class names like t3lib_div.
 * Also it will make them fixable and migrate them to new ones.
 */
class Typo3Update_Sniffs_Legacy_ClassnamesSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Contains mapping from old -> new class names.
     * @var array
     */
    protected $legacyClassnames = [];

    /**
     * @param string $mappingFile File containing php array for mapping.
     */
    public function __construct($mappingFile = __DIR__ . '/../../../../../LegacyClassnames.php')
    {
        $legacyClassnames = require $mappingFile;
        $this->legacyClassnames = $legacyClassnames['aliasToClassNameMapping'];
    }

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @see http://php.net/manual/en/tokens.php
     *
     * @return array(int)
     */
    public function register()
    {
        return [
            // T_USE,
            // T_NEW,
            // T_INSTANCEOF,
            T_IMPLEMENTS,
            // T_EXTENDS,
            // T_STRING,
        ];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $classnamePosition = $phpcsFile->findNext(T_STRING, $stackPtr);
        $classname = $tokens[$classnamePosition]['content'];

        if ($this->isLegacyClassname($classname)) {
            $fix = $phpcsFile->addFixableError(
                'Legacy classes are not allowed; found %s',
                $classnamePosition,
                'legacyClassname',
                [$classname]
            );

            if ($fix === true) {
                switch ($tokens[$stackPtr]['code']) {
                    case T_IMPLEMENTS:
                        $phpcsFile->fixer->replaceToken($classnamePosition, '\\' . $this->getNewClassname($classname));
                        break;

                    default:

                        break;
                }
            }
        }
    }

    /**
     * @param string $classname
     * @return bool
     */
    protected function isLegacyClassname($classname)
    {
        return isset($this->legacyClassnames[strtolower($classname)]);
    }

    /**
     * @param string $classname
     * @return string
     */
    protected function getNewClassname($classname)
    {
        return $this->legacyClassnames[strtolower($classname)];
    }
}
