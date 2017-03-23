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

use Typo3Update\Sniffs\OptionsAccessTrait;

/**
 * Singleton wrapper for mappings.
 *
 * Will check the configured file for whether a class is legacy and provides further methods.
 * Also can update to add new migrated class names.
 */
class Mapping
{
    use OptionsAccessTrait;

    // Singleton implementation - Start
    static protected $instance = null;
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new Mapping();
        }

        return static::$instance;
    }
    private function __clone()
    {
    }
    private function __wakeup()
    {
    }
    private function __construct()
    {
        // $mappingFile = $this->getMappingFile();
        $mappingFile = __DIR__ . '/../../../../../LegacyClassnames.php';

        $this->mappings = require $mappingFile;
    }
    // Singleton implementation - End

    /**
     * Contains mappings as defined by composer for alias mapping.
     * @var array
     */
    protected $mappings = [];

    /**
     * Checks whether a mapping exists for the given $classname,
     * indicating it's legacy.
     *
     * @param string $classname
     * @return bool
     */
    public function isLegacyClassname($classname)
    {
        return isset($this->mappings['aliasToClassNameMapping'][strtolower($classname)]);
    }

    /**
     * @param string $classname
     * @return string
     */
    public function getNewClassname($classname)
    {
        return $this->mappings['aliasToClassNameMapping'][strtolower($classname)];
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
    public function addLegacyClassname($legacyClassname, $newClassname)
    {
        $key = strtolower($legacyClassname);

        $this->mappings['aliasToClassNameMapping'][$key] = $newClassname;
        $this->mappings['classNameToAliasMapping'][$newClassname] = [$key => $key];
    }

    /**
     * Used to persist new mappings.
     */
    public function __destruct()
    {
        // For some reasons desctruct is called multiple times, while construct
        // is called once. Until we know the issue and fix it, this is our
        // workaround to not break the file and do stuff in an unkown instance.
        if ($this !== static::$instance) {
            return;
        }

        // $mappingFile = $this->getMappingFile();
        $mappingFile = __DIR__ . '/../../../../../LegacyClassnames.php';

        file_put_contents(
            $mappingFile,
            '<?php' . PHP_EOL . 'return ' . var_export($this->mappings, true) . ';'
        );
    }
}
