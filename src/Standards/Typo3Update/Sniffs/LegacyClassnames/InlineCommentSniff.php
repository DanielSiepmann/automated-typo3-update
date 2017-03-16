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
 * Migrate PHP inline comments, e.g. for IDEs.
 */
class Typo3Update_Sniffs_LegacyClassnames_InlineCommentSniff implements PHP_CodeSniffer_Sniff
{
    use \Typo3Update\Sniffs\LegacyClassnames\ClassnameCheckerTrait;

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
        return [
            T_COMMENT,
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
        if (substr($tokens[$stackPtr]['content'], 0, 9) !== '/* @var $') {
            return;
        }

        $commentParts = explode(' ', $tokens [$stackPtr ]['content']);
        if (count($commentParts) !== 5) {
            return;
        }

        $this->originalTokenContent = $tokens[$stackPtr]['content'];
        $this->addFixableError($phpcsFile, $stackPtr, $commentParts[3]);
    }

    /**
     * As token contains more then just class name, we have to build new content ourself.
     *
     * @param string $classname
     * @return string
     */
    protected function getTokenForReplacement($classname)
    {
        $token = explode(' ', $this->originalTokenContent);
        $token[3] = $classname;

        return implode(' ', $token);
    }
}
