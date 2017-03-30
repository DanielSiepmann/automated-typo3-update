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
use PHP_CodeSniffer_Sniff as PhpCsSniff;
use PHP_CodeSniffer_Tokens as Tokens;
use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class Typo3Update_Sniffs_Removed_GenericConstantUsageCallSniff implements PhpCsSniff
{
    use \Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;
    use \Typo3Update\Sniffs\OptionsAccessTrait;

    /**
     * Configuration to define removed constants.
     *
     * @var array
     */
    protected static $removedConstants = [];

    /**
     * Constant for the current sniff instance.
     * @var array
     */
    private $removedConstant = [];

    /**
     * TODO: Multiple files allowed, using glob ...
     * to allow splitting per ext (extbase, fluid, ...) and TYPO3 Version 7.1, 7.0, ...
     */
    public function __construct()
    {
        if (static::$removedConstants === []) {
            foreach ($this->getRemovedConstantConfigFiles() as $file) {
                static::$removedConstants = array_merge(
                    static::$removedConstants,
                    $this->prepareStructure(Yaml::parse(file_get_contents((string) $file)))
                );
            }
        }
    }

    /**
     * Prepares structure from config for later usage.
     *
     * @param array $oldStructure
     * @return array
     */
    protected function prepareStructure(array $oldStructure)
    {
        $typo3Versions = array_keys($oldStructure);
        $newStructure = [];

        foreach ($typo3Versions as $typo3Version) {
            foreach ($oldStructure[$typo3Version] as $constant => $config) {
                // Split static methods and methods.
                $split = preg_split('/::|->/', $constant);

                $newStructure[$constant] = $config;

                $newStructure[$constant]['static'] = strpos($constant, '::') !== false;
                $newStructure[$constant]['fqcn'] = null;
                $newStructure[$constant]['class'] = null;
                $newStructure[$constant]['constant'] = $split[0];
                $newStructure[$constant]['version_removed'] = $typo3Version;

                // If split contains two parts, it's a class with method
                if (isset($split[1])) {
                    $newStructure[$constant]['fqcn'] = $split[0];
                    $newStructure[$constant]['class'] = array_slice(
                        explode('\\', $newStructure[$constant]['fqcn']),
                        -1
                    )[0];
                    $newStructure[$constant]['constant'] = $split[1];
                }
            };
        }

        return $newStructure;
    }

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array<int>
     */
    public function register()
    {
        return Tokens::$constantNameTokens;
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
     */
    public function process(PhpCsFile $phpcsFile, $stackPtr)
    {
        if (!$this->isConstantCallRemoved($phpcsFile, $stackPtr)) {
            return;
        }

        $this->addWarning($phpcsFile, $stackPtr);
    }

    /**
     * Check whether constant at given point is removed.
     *
     * @return bool
     */
    protected function isConstantCallRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        if (!$this->isConstantCall($phpcsFile, $stackPtr)) {
            return false;
        }

        $tokens = $phpcsFile->getTokens();
        $staticPosition = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true, null, true);

        $constantName = $tokens[$stackPtr]['content'];
        $isStatic = false;
        $class = false;

        if ($staticPosition !== false) {
            $isStatic = $tokens[$staticPosition]['code'] === T_DOUBLE_COLON;
        }

        if ($isStatic) {
            $class = $phpcsFile->findPrevious(T_STRING, $staticPosition, null, false, null, true);
            if ($class !== false) {
                $class = $tokens[$class]['content'];
            }
        }

        return $this->getRemovedConstant($constantName, $class, $isStatic) !== [];
    }

    /**
     * Returns all matching removed constants for given arguments.
     *
     * Also prepares constants for later usages in $this->removedConstant.
     *
     * @param string $constantName
     * @param string $className The last part of the class name, splitted by namespaces.
     * @param bool $isStatic
     *
     * @return array
     */
    protected function getRemovedConstant($constantName, $className, $isStatic)
    {
        // We will not match any static method, without the class name, at least for now.
        // Otherwise we could handle them the same way as instance methods.
        if ($isStatic === true && $className === false) {
            return [];
        }

        $this->removedConstant = array_filter(
            static::$removedConstants,
            function ($config) use ($constantName, $isStatic, $className) {
                return $constantName === $config['constant']
                    && $isStatic === $config['static']
                    && (
                        $className === $config['class']
                        || $className === false
                    )
                    ;
            }
        );

        return $this->removedConstant;
    }

    /**
     * Returns configuration for currently checked constant.
     *
     * @return array
     */
    protected function getCurrentRemovedConstant()
    {
        $config = current($this->removedConstant);

        // TODO: Add exception if something went wrong?

        return $config;
    }

    /**
     * Add warning for the given token position.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $tokenPosition
     *
     * @return void
     */
    protected function addWarning(PhpCsFile $phpcsFile, $tokenPosition)
    {
        $phpcsFile->addWarning(
            'Legacy constant calls are not allowed; found %s. Removed in %s. %s. See: %s',
            $tokenPosition,
            $this->getConstantIdentifier(),
            [
                $this->getOldConstantCall(),
                $this->getRemovedVersion(),
                $this->getNewConstantCall(),
                $this->getDocsUrl(),
            ]
        );
    }

    /**
     * Identifier for configuring this specific error / warning through PHPCS.
     *
     * @return string
     */
    protected function getConstantIdentifier()
    {
        $config = $this->getCurrentRemovedConstant();
        return $config['class'] . '.' . $config['constant'];
    }

    /**
     * The original constant call, to allow user to check matches.
     *
     * As we match the constant name, that can be provided by multiple classes,
     * you should provide an example, so users can check that this is the
     * legacy one.
     *
     * @return string
     */
    protected function getOldConstantCall()
    {
        return  $this->getCurrentRemovedConstant();
    }

    /**
     * Returns TYPO3 version when the current constant was removed.
     *
     * To let user decide whether this is important for him.
     *
     * @return string
     */
    protected function getRemovedVersion()
    {
        return $this->getCurrentRemovedConstant()['version_removed'];
    }

    /**
     * The new function call, or information how to migrate.
     *
     * To provide feedback for user to ease migration.
     *
     * @return string
     */
    protected function getNewConstantCall()
    {
        $newCall = $this->getCurrentRemovedConstant()['newConstantCall'];
        if ($newCall !== null) {
            return $newCall;
        }
        return 'There is no replacement, just remove call';
    }

    /**
     * Allow user to lookup the official docs related to this deprecation / breaking change.
     *
     * @return string
     */
    protected function getDocsUrl()
    {
        return $this->getCurrentRemovedConstant()['docsUrl'];
    }
}
