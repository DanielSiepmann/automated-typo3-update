<?php
namespace Typo3Update\Sniffs\LegacyClassnames;

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

use PHP_CodeSniffer_File as PhpcsFile;

/**
 * Provide common uses for all sniffs.
 */
trait ClassnameCheckerTrait
{
    /**
     * Contains mapping from old -> new class names.
     * @var array
     */
    private $legacyClassnames = [];

    /**
     * @param string $mappingFile File containing php array for mapping.
     */
    private function initialize($mappingFile = __DIR__ . '/../../../../../LegacyClassnames.php')
    {
        if ($this->legacyClassnames !== []) {
            return;
        }

        $legacyClassnames = require $mappingFile;
        $this->legacyClassnames = $legacyClassnames['aliasToClassNameMapping'];
    }

    /**
     * Checks whether a mapping exists for the given $classname,
     * indicating it's legacy.
     *
     * @param string $classname
     * @return bool
     */
    private function isLegacyClassname($classname)
    {
        $this->initialize();
        return isset($this->legacyClassnames[strtolower($classname)]);
    }

    /**
     * @param string $classname
     * @return string
     */
    private function getNewClassname($classname)
    {
        $this->initialize();
        return $this->legacyClassnames[strtolower($classname)];
    }

    /**
     * Add an fixable error if given $classname is legacy.
     *
     * @param PhpcsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     */
    public function addFixableError(PhpcsFile $phpcsFile, $classnamePosition, $classname)
    {
        if ($this->isLegacyClassname($classname) === false) {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            'Legacy classes are not allowed; found "%s", use "%s" instead',
            $classnamePosition,
            'legacyClassname',
            [$classname, $this->getNewClassname($classname)]
        );

        if ($fix === true) {
            $phpcsFile->fixer->replaceToken(
                $classnamePosition,
                $this->getTokenForReplacement('\\' . $this->getNewClassname($classname))
            );
        }
    }

    /**
     * String to use for replacing / fixing the token.
     * Default is class name itself, can be overwritten in sniff for special behaviour.
     *
     * @param string $classname
     * @return string
     */
    protected function getTokenForReplacement($classname)
    {
        return $classname;
    }
}
