<?php
namespace Typo3Update\Feature;

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

use PHP_CodeSniffer as PhpCs;
use PHP_CodeSniffer_File as PhpCsFile;
use PHP_CodeSniffer_Sniff as PhpCsSniff;

/**
 * This feature will add fixable errors for old legacy classnames.
 *
 * Can be attached to sniffs returning classnames.
 */
class RemovedClassFeature implements FeatureInterface
{
    /**
     * Process like a PHPCS Sniff.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     *
     * @return void
     */
    public function process(PhpCsFile $phpcsFile, $classnamePosition, $classname)
    {
        if ($this->isClassnameRemoved($classname) === false) {
            return;
        }

        $phpcsFile->addError(
            'Removed classes are not allowed; found "%s", use "%s" instead',
            $classnamePosition,
            'removedClassname',
            [$classname]
        );
    }
}