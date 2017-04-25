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

use PHP_CodeSniffer_File as PhpCsFile;
use PHP_CodeSniffer_Sniff as PhpCsSniff;
use Typo3Update\Feature\FeaturesSupport;

/**
 * Provide common uses for all sniffs, regarding class name checks.
 */
abstract class AbstractClassnameChecker implements PhpCsSniff
{
    use FeaturesSupport;

    /**
     * Define whether the T_STRING default behaviour should be checked before
     * or after the $stackPtr.
     *
     * @return bool
     */
    protected function shouldLookBefore()
    {
        return false;
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
     *
     * @SuppressWarnings(PHPMD.ElseExpression) This is for performance reason.
     */
    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($this->shouldLookBefore()) {
            $classnamePosition = $phpcsFile->findPrevious(T_STRING, $stackPtr);
        } else {
            $classnamePosition = $phpcsFile->findNext(T_STRING, $stackPtr);
        }

        if ($classnamePosition === false) {
            return;
        }

        $classname = $tokens[$classnamePosition]['content'];
        $this->processFeatures($phpcsFile, $classnamePosition, $classname);
    }
}
