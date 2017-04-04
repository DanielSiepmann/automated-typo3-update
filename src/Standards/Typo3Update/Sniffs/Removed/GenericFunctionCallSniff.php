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
 * Sniff that handles all calls to removed functions.
 *
 * Removed functions are configured using YAML-Files, for examples see src/Standards/Typo3Update/Configuration/Removed/Functions/7.0.yaml
 * Also check out the configuration options in Readme.rst.
 */
class Typo3Update_Sniffs_Removed_GenericFunctionCallSniff implements PhpCsSniff
{
    use \Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;
    use \Typo3Update\Sniffs\OptionsAccessTrait;

    /**
     * Configuration to define removed functions.
     *
     * @var array
     */
    protected static $configuredFunctions = [];

    /**
     * Function for the current sniff instance.
     * @var array
     */
    private $removedFunctions = [];

    /**
     * TODO: Multiple files allowed, using glob ...
     * to allow splitting per ext (extbase, fluid, ...) and TYPO3 Version 7.1, 7.0, ...
     */
    public function __construct()
    {
        if (static::$configuredFunctions === []) {
            foreach ($this->getRemovedFunctionConfigFiles() as $file) {
                static::$configuredFunctions = array_merge(
                    static::$configuredFunctions,
                    $this->prepareStructure(Yaml::parse(file_get_contents((string) $file)))
                );
            }
        }
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

        foreach ($typo3Versions as $typo3Version => $functions) {
            foreach ($functions as $function => $config) {
                // Split static methods and methods.
                $split = preg_split('/::|->/', $function);

                $newStructure[$function] = $config;

                $newStructure[$function]['static'] = strpos($function, '::') !== false;
                $newStructure[$function]['fqcn'] = null;
                $newStructure[$function]['class'] = null;
                $newStructure[$function]['function'] = $split[0];
                $newStructure[$function]['version_removed'] = $typo3Version;

                // If split contains two parts, it's a class with method
                if (isset($split[1])) {
                    $newStructure[$function]['fqcn'] = $split[0];
                    $newStructure[$function]['class'] = array_slice(
                        explode('\\', $newStructure[$function]['fqcn']),
                        -1
                    )[0];
                    $newStructure[$function]['function'] = $split[1];
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
        return Tokens::$functionNameTokens;
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
        if (!$this->isFunctionCallRemoved($phpcsFile, $stackPtr)) {
            return;
        }

        $this->addWarning($phpcsFile, $stackPtr);
    }

    /**
     * Check whether function at given point is removed.
     *
     * @return bool
     */
    protected function isFunctionCallRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        if (!$this->isFunctionCall($phpcsFile, $stackPtr)) {
            return false;
        }

        $tokens = $phpcsFile->getTokens();
        $staticPosition = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true, null, true);

        $functionName = $tokens[$stackPtr]['content'];
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

        $this->removedFunctions = $this->getMatchingRemovedFunctions($functionName, $class, $isStatic);
        return $this->removedFunctions !== [];
    }

    /**
     * Returns all matching removed functions for given arguments.
     *
     * @param string $functionName
     * @param string $className The last part of the class name, splitted by namespaces.
     * @param bool $isStatic
     *
     * @return void
     */
    protected function getMatchingRemovedFunctions($functionName, $className, $isStatic)
    {
        // We will not match any static method, without the class name, at least for now.
        // Otherwise we could handle them the same way as instance methods.
        if ($isStatic === true && $className === false) {
            return;
        }

        return array_filter(
            static::$configuredFunctions,
            function ($config) use ($functionName, $isStatic, $className) {
                return $functionName === $config['function']
                    && $isStatic === $config['static']
                    && (
                        $className === $config['class']
                        || $className === false
                    )
                ;
            }
        );
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
        foreach ($this->removedFunctions as $function) {
            $phpcsFile->addWarning(
                'Legacy function calls are not allowed; found %s. Removed in %s. %s. See: %s',
                $tokenPosition,
                $this->getFunctionIdentifier($function),
                [
                    $this->getOldfunctionCall($function),
                    $this->getRemovedVersion($function),
                    $this->getNewFunctionCall($function),
                    $this->getDocsUrl($function),
                ]
            );
        }
    }

    /**
     * Identifier for configuring this specific error / warning through PHPCS.
     *
     * @param array $config The converted structure for a single function.
     *
     * @return string
     */
    protected function getFunctionIdentifier(array $config)
    {
        return $config['class'] . '.' . $config['function'];
    }

    /**
     * The original function call, to allow user to check matches.
     *
     * As we match the function name, that can be provided by multiple classes,
     * you should provide an example, so users can check that this is the
     * legacy one.
     *
     * @param array $config The converted structure for a single function.
     *
     * @return string
     */
    protected function getOldFunctionCall(array $config)
    {
        $concat = '->';
        if ($config['static']) {
            $concat = '::';
        }
        return $config['fqcn'] . $concat . $config['function'];
    }

    /**
     * Returns TYPO3 version when the current function was removed.
     *
     * To let user decide whether this is important for him.
     *
     * @param array $config The converted structure for a single function.
     *
     * @return string
     */
    protected function getRemovedVersion(array $config)
    {
        return $config['version_removed'];
    }

    /**
     * The new function call, or information how to migrate.
     *
     * To provide feedback for user to ease migration.
     *
     * @param array $config The converted structure for a single function.
     *
     * @return string
     */
    protected function getNewFunctionCall(array $config)
    {
        $newCall = $config['newFunctionCall'];
        if ($newCall !== null) {
            return $newCall;
        }
        return 'There is no replacement, just remove call';
    }

    /**
     * Allow user to lookup the official docs related to this deprecation / breaking change.
     *
     * @param array $config The converted structure for a single function.
     *
     * @return string
     */
    protected function getDocsUrl(array $config)
    {
        return $config['docsUrl'];
    }
}
