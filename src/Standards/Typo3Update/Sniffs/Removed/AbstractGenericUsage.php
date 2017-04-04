<?php
namespace Typo3Update\Sniffs\Removed;

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
 * Contains common functionality for removed code like constants or functions.
 *
 * Removed parts are configured using YAML-Files, for examples see src/Standards/Typo3Update/Configuration/Removed/Constants/7.0.yaml
 * Also check out the configuration options in Readme.rst.
 */
abstract class AbstractGenericUsage implements PhpCsSniff
{
    use \Typo3Update\Sniffs\ExtendedPhpCsSupportTrait;
    use \Typo3Update\Sniffs\OptionsAccessTrait;

    /**
     * Configuration to define removed code.
     *
     * @var array
     */
    protected $configured = [];

    /**
     * Constant for the current sniff instance.
     * @var array
     */
    protected $removed = [];

    /**
     * TODO: Multiple files allowed, using glob ...
     * to allow splitting per ext (extbase, fluid, ...) and TYPO3 Version 7.1, 7.0, ...
     */
    public function __construct()
    {
        if ($this->configured === []) {
            foreach ($this->getRemovedConfigFiles() as $file) {
                $this->configured = array_merge(
                    $this->configured,
                    $this->prepareStructure(Yaml::parse(file_get_contents((string) $file)))
                );
            }
        }
    }

    /**
     * Return file names containing removed configurations.
     *
     * @return array<string>
     */
    abstract protected function getRemovedConfigFiles();

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
                // Split static methods and methods.
                $split = preg_split('/::|->/', $removed);

                $newStructure[$removed] = $config;

                $newStructure[$removed]['static'] = strpos($removed, '::') !== false;
                $newStructure[$removed]['fqcn'] = null;
                $newStructure[$removed]['class'] = null;
                $newStructure[$removed]['name'] = $split[0];
                $newStructure[$removed]['version_removed'] = $typo3Version;

                // If split contains two parts, it's a class with method
                if (isset($split[1])) {
                    $newStructure[$removed]['fqcn'] = $split[0];
                    $newStructure[$removed]['class'] = array_slice(
                        explode('\\', $newStructure[$removed]['fqcn']),
                        -1
                    )[0];
                    $newStructure[$removed]['name'] = $split[1];
                }
            };
        }

        return $newStructure;
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
        if (!$this->isRemoved($phpcsFile, $stackPtr)) {
            return;
        }

        $this->addWarning($phpcsFile, $stackPtr);
    }

    /**
     * Check whether the current token is removed.
     *
     * @param PhpCsFile $phpcsFile
     * @param int $stackPtr
     * @return bool
     */
    protected function isRemoved(PhpCsFile $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $staticPosition = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true, null, true);

        $name = $tokens[$stackPtr]['content'];
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

        $this->removed = $this->getMatchingRemoved($name, $class, $isStatic);
        return $this->removed !== [];
    }

    /**
     * Returns all matching removed functions for given arguments.
     *
     * @param string $name
     * @param string $className The last part of the class name, splitted by namespaces.
     * @param bool $isStatic
     *
     * @return array
     */
    protected function getMatchingRemoved($name, $className, $isStatic)
    {
        // We will not match any static method, without the class name, at least for now.
        // Otherwise we could handle them the same way as instance methods.
        if ($isStatic === true && $className === false) {
            return [];
        }

        return array_filter(
            $this->configured,
            function ($config) use ($name, $isStatic, $className) {
                return $name === $config['name']
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
        foreach ($this->removed as $constant) {
            $phpcsFile->addWarning(
                'Legacy calls are not allowed; found %s. Removed in %s. %s. See: %s',
                $tokenPosition,
                $this->getIdentifier($constant),
                [
                    $this->getOldUsage($constant),
                    $this->getRemovedVersion($constant),
                    $this->getReplacement($constant),
                    $this->getDocsUrl($constant),
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
    protected function getIdentifier(array $config)
    {
        $name = $config['name'];
        if ($config['class']) {
            $name = $config['class'] . '.' . $name;
        }

        return $name;
    }

    /**
     * The original constant call, to allow user to check matches.
     *
     * As we match the constant name, that can be provided by multiple classes,
     * you should provide an example, so users can check that this is the
     * legacy one.
     *
     * @param array $config The converted structure for a single constant.
     *
     * @return string
     */
    abstract protected function getOldUsage(array $config);

    /**
     * Returns TYPO3 version when the current constant was removed.
     *
     * To let user decide whether this is important for him.
     *
     * @param array $config The converted structure for a single constant.
     *
     * @return string
     */
    protected function getRemovedVersion(array $config)
    {
        return $config['version_removed'];
    }

    /**
     * The new constant call, or information how to migrate.
     *
     * To provide feedback for user to ease migration.
     *
     * @param array $config The converted structure for a single constant.
     *
     * @return string
     */
    protected function getReplacement(array $config)
    {
        $newCall = $config['replacement'];
        if ($newCall !== null) {
            return $newCall;
        }
        return 'There is no replacement, just remove call';
    }

    /**
     * Allow user to lookup the official docs related to this deprecation / breaking change.
     *
     * @param array $config The converted structure for a single constant.
     *
     * @return string
     */
    protected function getDocsUrl(array $config)
    {
        return $config['docsUrl'];
    }
}
