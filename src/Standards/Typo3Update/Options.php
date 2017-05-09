<?php
namespace Typo3Update;

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
use Symfony\Component\Yaml\Yaml;

/**
 * Wrapper to retrieve options from PhpCs with defaults.
 */
class Options
{
    /**
     * Returns the configured vendor, e.g. to generate new namespaces.
     *
     * @return string
     */
    public static function getVendor()
    {
        $vendor = static::getOptionWithDefault(
            'vendor',
            'YourCompany'
        );

        return trim($vendor, '\\/');
    }

    /**
     * Returns the configured file path containing the mappings for classes, interfaced and traits.
     *
     * @return string
     */
    public static function getMappingFile()
    {
        return (string) static::getOptionWithDefault(
            'mappingFile',
            __DIR__ . '/../../../LegacyClassnames.php'
        );
    }

    /**
     * Returns an array of absolute file names containing removed function configurations.
     *
     * @return array<string>
     */
    public static function getRemovedFunctionConfigFiles()
    {
        return static::getOptionFileNames(
            'removedFunctionConfigFiles',
            __DIR__ . '/Configuration/Removed/Functions/*.yaml'
        );
    }

    /**
     * Returns an array of absolute file names containing removed constant configurations.
     *
     * @return array<string>
     */
    public static function getRemovedConstantConfigFiles()
    {
        return static::getOptionFileNames(
            'removedConstantConfigFiles',
            __DIR__ . '/Configuration/Removed/Constants/*.yaml'
        );
    }

    /**
     * Returns an array of absolute file names containing removed typoscript.
     *
     * @return array<string>
     */
    public static function getRemovedTypoScriptConfigFiles()
    {
        return static::getOptionFileNames(
            'removedTypoScript',
            __DIR__ . '/Configuration/Removed/TypoScript/*.yaml'
        );
    }

    /**
     * Returns an array of absolute file names containing removed typoscript constants.
     *
     * @return array<string>
     */
    public static function getRemovedTypoScriptConstantConfigFiles()
    {
        return static::getOptionFileNames(
            'removedTypoScriptConstant',
            __DIR__ . '/Configuration/Removed/TypoScriptConstant/*.yaml'
        );
    }

    /**
     * Returns an array of absolute file names containing removed class configurations.
     *
     * @return array<string>
     */
    public static function getRemovedClassConfigFiles()
    {
        return static::getOptionFileNames(
            'removedClassConfigFiles',
            __DIR__ . '/Configuration/Removed/Classes/*.yaml'
        );
    }

    /**
     * Returns an array of absolute file names containing removed globals configurations.
     *
     * @return array<string>
     */
    public static function getRemovedGlobalConfigFiles()
    {
        return static::getOptionFileNames(
            'removedGlobalConfigFiles',
            __DIR__ . '/Configuration/Removed/Globals/*.yaml'
        );
    }

    /**
     * Get the option by optionName, if not defined, use default.
     *
     * @param string $optionName
     * @param mixed $default
     *
     * @return mixed
     */
    private static function getOptionWithDefault($optionName, $default)
    {
        $option = PhpCs::getConfigData($optionName);
        if (!$option) {
            $option = $default;
        }

        return $option;
    }

    /**
     * Get the configured features.
     *
     * @return array
     */
    public static function getFeaturesConfiguration()
    {
        $option = [];
        $fileNames = static::getOptionFileNames(
            'features',
            __DIR__ . '/Configuration/Features/*.yaml'
        );

        foreach ($fileNames as $file) {
            $content = Yaml::parse(file_get_contents((string) $file));
            if ($content === null) {
                $content = [];
            }
            $option = array_merge($option, $content);
        }

        return $option;
    }

    /**
     * Get file names defined by option using optionName, if not defined, use default.
     *
     * TODO: Multiple files allowed, using glob ...
     * to allow splitting per ext (extbase, fluid, ...) and TYPO3 Version 7.1, 7.0, ...
     *
     * @param string $optionName
     * @param mixed $default
     *
     * @return array<string>
     */
    protected static function getOptionFileNames($optionName, $default)
    {
        $files = static::getOptionWithDefault($optionName, $default);
        $fileNames = [];

        foreach ((new \GlobIterator($files)) as $file) {
            $fileNames[] = (string) $file;
        }

        return $fileNames;
    }
}
