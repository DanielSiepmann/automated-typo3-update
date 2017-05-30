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
use Typo3Update\Feature\FeaturesSupport;

/**
 * Handle PHP Doc comments.
 *
 * E.g. annotations like @param or @return, see $allowedTags.
 *
 * Will do nothing itself, but call features.
 */
class PhpDocCommentSniff implements PhpCsSniff
{
    use FeaturesSupport;

    /**
     * The configured tags will be processed.
     * @var array<string>
     */
    public $allowedTags = ['@param', '@return', '@var', '@validate'];

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return [T_DOC_COMMENT_TAG];
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
        if (!in_array($tokens[$stackPtr]['content'], $this->allowedTags)) {
            return;
        }
        $classnamePosition = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $stackPtr);
        if ($classnamePosition === false) {
            return;
        }
        $classnames = array_filter(preg_split('/\||\s|\<|\>|\(/', $tokens[$classnamePosition]['content']));

        foreach ($classnames as $classname) {
            $this->processFeatures($phpcsFile, $classnamePosition, $classname);
        }
    }
}
