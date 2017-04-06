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
use Typo3Update\Sniffs\LegacyClassnames\AbstractClassnameChecker;

/**
 * Migrate PHP inline comments, e.g. for IDEs.
 */
class Typo3Update_Sniffs_LegacyClassnames_InlineCommentSniff extends AbstractClassnameChecker
{
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
     * @param PhpCsFile $phpcsFile The file where the token was found.
     * @param int                  $stackPtr  The position in the stack where
     *                                        the token was found.
     *
     * @return void
     */
    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $this->originalTokenContent = $tokens[$stackPtr]['content'];
        $commentParts = preg_split('/\s+/', $this->originalTokenContent);

        if (count($commentParts) !== 5 || $commentParts[1] !== '@var'
            || ($commentParts[2][0] !== '$' && $commentParts[3][0] !== '$')) {
            return;
        }

        $this->addFixableError($phpcsFile, $stackPtr, $commentParts[$this->getClassnamePosition($commentParts)]);
    }

    /**
     * As Classname can be found as first or second argument of @var, we have
     * to check where it is.
     *
     * @param array $commentParts
     * @return int
     */
    protected function getClassnamePosition(array $commentParts)
    {
        if ($commentParts[3][0] === '$') {
            return 2;
        }

        return 3;
    }

    /**
     * As token contains more then just class name, we have to build new content ourself.
     *
     * @param string $newClassname
     * @param string $originalClassname
     * @return string
     */
    protected function getTokenForReplacement($newClassname, $originalClassname)
    {
        $token = preg_split('/\s+/', $this->originalTokenContent);
        $token[$this->getClassnamePosition($token)] = $newClassname;

        return implode(' ', $token);
    }
}
