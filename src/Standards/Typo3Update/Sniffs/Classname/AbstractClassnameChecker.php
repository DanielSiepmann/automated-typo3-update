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
 *
 * Will do nothing but calling configured features, allowing new extending
 * sniffs to find further class names.
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
        try {
            if ($this->shouldLookBefore()) {
                list($classnamePosition, $classname) = $this->getBefore($phpcsFile, $stackPtr);
            } else {
                list($classnamePosition, $classname) = $this->getAfter($phpcsFile, $stackPtr);
            }
        } catch (\UnexpectedValueException $e) {
            return;
        }

        $this->processFeatures($phpcsFile, $classnamePosition, $classname);
    }

    /**
     * Get position and classname before current stack pointer.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr  The position in the stack where
     *
     * @return array
     */
    protected function getBefore(PhpCsFile $phpcsFile, $stackPtr)
    {
        $possibleStart = $phpcsFile->findPrevious([
            T_STRING, T_NS_SEPARATOR,
        ], $stackPtr - 1, null, true, null, true);
        if ($possibleStart === false) {
            throw new \UnexpectedValueException('Could not find start of classname.', 1494319966);
        }

        $classnamePosition = $phpcsFile->findNext(T_STRING, $possibleStart);
        if ($classnamePosition === false) {
            throw new \UnexpectedValueException('Could not find start of classname.', 1494319966);
        }

        $end = $phpcsFile->findNext([
            T_STRING, T_NS_SEPARATOR
        ], $classnamePosition + 1, $stackPtr + 1, true, null, true);
        if ($end === false) {
            throw new \UnexpectedValueException('Could not find end of classname.', 1494319651);
        }

        $classname = $phpcsFile->getTokensAsString($classnamePosition, $end - $classnamePosition);

        return [$classnamePosition, $classname];
    }

    /**
     * Get position and classname after current stack pointer.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr  The position in the stack where
     *
     * @return array
     */
    protected function getAfter(PhpCsFile $phpcsFile, $stackPtr)
    {
        $classnamePosition = $phpcsFile->findNext(T_STRING, $stackPtr);
        if ($classnamePosition === false) {
            throw new \UnexpectedValueException('Could not find start of classname.', 1494319665);
        }

        $end = $phpcsFile->findNext([T_STRING, T_NS_SEPARATOR], $classnamePosition, null, true, null, true);
        if ($end === false) {
            throw new \UnexpectedValueException('Could not find end of classname.', 1494319651);
        }

        $classname = $phpcsFile->getTokensAsString($classnamePosition, $end - $classnamePosition);

        return [$classnamePosition, $classname];
    }
}
