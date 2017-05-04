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
class LegacyClassnameFeature implements FeatureInterface
{
    /**
     * A list of extension names that might contain legacy class names.
     * Used to check clas names for warnings.
     *
     * Configure through ruleset.xml.
     *
     * @var array<string>
     */
    public $legacyExtensions = ['Extbase', 'Fluid'];

    /**
     * @var LegacyClassnameMapping
     */
    protected $legacyMapping;

    /**
     * @var PhpCsSniff
     */
    protected $sniff;

    public function __construct(PhpCsSniff $sniff)
    {
        $this->sniff = $sniff;
        $this->legacyMapping = LegacyClassnameMapping::getInstance();
    }

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
        $classname = trim($classname, '\\\'"'); // Remove trailing slash, and quotes.
        $this->addMaybeWarning($phpcsFile, $classnamePosition, $classname);

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
            $this->replaceLegacyClassname($phpcsFile, $classnamePosition, $classname);
        }
    }

    /**
     * Checks whether a mapping exists for the given $classname,
     * indicating it's legacy.
     *
     * @param string $classname
     * @return bool
     */
    protected function isLegacyClassname($classname)
    {
        if (get_class($this->sniff) === \Typo3Update_Sniffs_Classname_StringSniff::class) {
            return false;
        }

        return $this->legacyMapping->isCaseInsensitiveLegacyClassname($classname);
    }

    /**
     * Guesses whether the given classname is legacy.  Will not check
     * isLegacyClassname
     *
     * @param string $classname
     * @return bool
     */
    protected function isMaybeLegacyClassname($classname)
    {
        if (strpos($classname, 'Tx_') === false) {
            return false;
        }

        $extensionName = call_user_func(function ($classname) {
            $nameParts = explode('_', $classname);
            return $nameParts[1];
        }, $classname);

        if (!in_array($extensionName, $this->legacyExtensions)
            && get_class($this->sniff) !== \Typo3Update_Sniffs_Classname_StringSniff::class
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param string $classname
     * @return string
     */
    protected function getNewClassname($classname)
    {
        return $this->legacyMapping->getNewClassname($classname);
    }

    /**
     * Add an warning if given $classname is maybe legacy.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     */
    protected function addMaybeWarning(PhpCsFile $phpcsFile, $classnamePosition, $classname)
    {
        if ($this->isLegacyClassname($classname) || $this->isMaybeLegacyClassname($classname) === false) {
            return;
        }

        $phpcsFile->addWarning(
            'Legacy classes are not allowed; found %s that might be a legacy class that does not exist anymore',
            $classnamePosition,
            'mightBeLegacyClassname',
            [$classname]
        );
    }

    /**
     * Replaces the classname at $classnamePosition with $classname in $phpcsFile.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     */
    protected function replaceLegacyClassname(
        PhpCsFile $phpcsFile,
        $classnamePosition,
        $classname
    ) {
        $prefix = '\\';
        if ($this->useEmptyPrefix($phpcsFile, $classnamePosition)) {
            $prefix = '';
        }

        $newClassname = str_replace(
            $classname,
            $prefix . $this->getNewClassname($classname),
            $phpcsFile->getTokens()[$classnamePosition]['content']
        );

        // Handle double quotes, with special escaping.
        if ($newClassname[0] === '"') {
            $newClassname = '"\\\\' . str_replace('\\', '\\\\', ltrim(substr($newClassname, 1), '\\'));
        }

        $phpcsFile->fixer->replaceToken($classnamePosition, $newClassname);
    }

    /**
     * Check whether new class name for replacment should not contain the "\" as prefix.
     *
     * @return bool
     */
    protected function useEmptyPrefix(PhpCsFile $phpcsFile, $classnamePosition)
    {
        // Use statements don't start with T_NS_SEPARATOR.
        if (get_class($this->sniff) === \Typo3Update_Sniffs_Classname_UseSniff::class) {
            return true;
        }
        // If T_NS_SEPARATOR is already present before, don't add again.
        if ($phpcsFile->getTokens()[$classnamePosition -1]['code'] === T_NS_SEPARATOR) {
            return true;
        }
        // If inside string starting with T_NS_SEPARATOR don't add again.
        if (isset($phpcsFile->getTokens()[$classnamePosition]['content'][1])
            && $phpcsFile->getTokens()[$classnamePosition]['content'][1] === '\\'
        ) {
            return true;
        }

        return false;
    }
}
