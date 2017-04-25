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
use Typo3Update\FeatureInterface;
use Typo3Update\Feature\LegacyClassnameMapping;

/**
 *
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
     * Used by some sniffs to keep original token for replacement.
     *
     * E.g. when Token itself is a whole inline comment, and we just want to replace the classname within.
     *
     * @var string
     */
    protected $originalTokenContent = '';

    public function __construct()
    {
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
        return $this->legacyMapping->isLegacyClassname($classname);
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

        if (!in_array($extensionName, $this->legacyExtensions)) {
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
     * Use to add new mappings found during parsing.
     * E.g. in MissingNamespaceSniff old class definitions are fixed and a new mapping exists afterwards.
     *
     * @param string $legacyClassname
     * @param string $newClassname
     *
     * @return void
     */
    protected function addLegacyClassname($legacyClassname, $newClassname)
    {
        $this->legacyMapping->addLegacyClassname($legacyClassname, $newClassname);
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
     * @param bool $forceEmptyPrefix Defines whether '\\' prefix should be checked or always be left out.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function replaceLegacyClassname(
        PhpCsFile $phpcsFile,
        $classnamePosition,
        $classname,
        $forceEmptyPrefix = false
    ) {
        $prefix = '\\';
        if ($forceEmptyPrefix || $phpcsFile->getTokens()[$classnamePosition -1]['code'] === T_NS_SEPARATOR) {
            $prefix = '';
        }

        $phpcsFile->fixer->replaceToken(
            $classnamePosition,
            $this->getTokenForReplacement($prefix . $this->getNewClassname($classname), $classname, $phpcsFile)
        );
    }

    /**
     * String to use for replacing / fixing the token.
     * Default is class name itself, can be overwritten in sniff for special behaviour.
     *
     * @param string $newClassname
     * @param string $originalClassname
     * @param PhpCsFile $phpcsFile
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) We need to match the signature.
     */
    protected function getTokenForReplacement($newClassname, $originalClassname, PhpCsFile $phpcsFile)
    {
        return $newClassname;
    }

    /**
     * Use this inside your getTokenForReplacement if $classname is inside a string.
     * Strings will be converted to single quotes.
     *
     * @param string $classname
     * @return string
     */
    protected function getTokenReplacementForString($classname)
    {
        $stringSign = $this->originalTokenContent[0];
        $token = explode($stringSign, $this->originalTokenContent);
        $token[1] = $classname;

        // Migrate double quote to single quote.
        // This way no escaping of backslashes in class names is necessary.
        if ($stringSign === '"') {
            $stringSign = "'";
        }

        return implode($stringSign, $token);
    }
}
