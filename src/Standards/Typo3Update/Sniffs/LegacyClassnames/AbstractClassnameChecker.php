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

use PHP_CodeSniffer as PhpCs;
use PHP_CodeSniffer_File as PhpCsFile;
use Typo3Update\Sniffs\LegacyClassnames\Mapping;
use Typo3Update\Sniffs\Options;
use Typo3Update\Sniffs\Removed\AbstractGenericUsage;

/**
 * Provide common uses for all sniffs, regarding class name checks.
 *
 * Beside legacy class names, we also check removed class names.
 */
abstract class AbstractClassnameChecker extends AbstractGenericUsage
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
     * @var Mapping
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
        parent::__construct();
        $this->legacyMapping = Mapping::getInstance();
    }

    /**
     * Return file names containing removed configurations.
     *
     * @return array<string>
     */
    protected function getRemovedConfigFiles()
    {
        return Options::getRemovedClassConfigFiles();
    }

    /**
     * Prepares structure from config for later usage.
     *
     * @param array $typo3Versions
     * @return array
     */
    protected function prepareStructure(array $typo3Versions)
    {
        $newStructure = [];

        foreach ($typo3Versions as $typo3Version => $removals) {
            foreach ($removals as $removed => $config) {
                if ($removed[0] !== '\\') {
                    $removed = '\\' . $removed;
                }

                $newStructure[$removed] = $config;
                $newStructure[$removed]['class'] = $removed;
                $newStructure[$removed]['name'] = str_replace('\\', '_', substr($removed, 1));
                $newStructure[$removed]['version_removed'] = $typo3Version;
            }
        }

        return $newStructure;
    }

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
        $this->addFixableError($phpcsFile, $classnamePosition, $classname);

        $this->handleRemoved($phpcsFile, $classnamePosition);
    }

    /**
     * Handle removed classes.
     *
     * This will check whether the class at given $classnamePosition was removed in any version and add a warning.
     *
     * @param PhpCsFile $phpcsFile The file where the token was found.
     * @param int $classnamePosition
     * @return void
     */
    protected function handleRemoved(PhpCsFile $phpcsFile, $classnamePosition)
    {
        if ($this->isRemoved($phpcsFile, $classnamePosition)) {
            $this->addMessage($phpcsFile, $classnamePosition);
        }
    }

    /**
     * Check whether the current token is removed.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @return bool
     */
    protected function isRemoved(PhpCsFile $phpcsFile, $classnamePosition)
    {
        $this->removed = [];
        $classname = $phpcsFile->getTokens()[$classnamePosition]['content'];

        if ($classname[0] !== '\\') {
            $classname = '\\' . $classname;
        }

        if (isset($this->configured[$classname])) {
            $this->removed = [
                $this->configured[$classname]
            ];
        }

        return $this->removed !== [];
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
    private function isMaybeLegacyClassname($classname)
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
     * Add an fixable error if given $classname is legacy.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     */
    public function addFixableError(PhpCsFile $phpcsFile, $classnamePosition, $classname)
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
     * Add an warning if given $classname is maybe legacy.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $classnamePosition
     * @param string $classname
     */
    private function addMaybeWarning(PhpCsFile $phpcsFile, $classnamePosition, $classname)
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

    /**
     * The original call, to allow user to check matches.
     *
     * As we match the name, that can be provided by multiple classes, you
     * should provide an example, so users can check that this is the legacy
     * one.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getOldUsage(array $config)
    {
        return $config['class'];
    }

    /**
     * Identifier for configuring this specific error / warning through PHPCS.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getIdentifier(array $config)
    {
        return $config['name'];
    }
}
