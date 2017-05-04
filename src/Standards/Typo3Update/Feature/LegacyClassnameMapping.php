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

use Typo3Update\Options;

/**
 * Singleton wrapper for mappings.
 *
 * Will check the configured file for whether a class is legacy and provides further methods.
 * Also can update to add new migrated class names.
 */
final class LegacyClassnameMapping
{
    // Singleton implementation - Start
    static protected $instance = null;
    /**
     * Get the singleton instance.
     *
     * @return Mapping
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new LegacyClassnameMapping();
        }

        return static::$instance;
    }
    // @codeCoverageIgnoreStart
    private function __clone()
    {
    }
    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) We just want to implement singleton pattern.
     */
    private function __wakeup()
    {
    }
    // @codeCoverageIgnoreEnd
    private function __construct()
    {
        if (is_file(Options::getMappingFile())) {
            $this->mappings = require Options::getMappingFile();
            $this->buildKeyArray($this->mappings, $this->mappingsKeys);
        }

        $this->typo3Mappings = require implode(DIRECTORY_SEPARATOR, [
            __DIR__, '..', 'Configuration', '',
        ]) . 'LegacyClassnames.php';
        $this->buildKeyArray($this->typo3Mappings, $this->typo3MappingsKeys);
    }
    // Singleton implementation - End

    /**
     * @var array
     */
    protected $typo3Mappings = [];
    /**
     * @var array
     */
    protected $typo3MappingsKeys = [];

    /**
     * @var array
     */
    protected $mappings = [];
    /**
     * @var array
     */
    protected $mappingsKeys = [];

    /**
     * @var bool
     */
    protected $dirty = false;

    /**
     * @param array $originalArray
     * @param array $targetVariable
     */
    protected function buildKeyArray($originalArray, &$targetVariable)
    {
        foreach (array_keys($originalArray) as $key) {
            $targetVariable[strtolower($key)] = $key;
        }
    }

    /**
     * Checks whether a mapping exists for the given $classname,
     * indicating it's legacy.
     *
     * @param string $classname
     * @return bool
     */
    public function isLegacyClassname($classname)
    {
        return $this->isLegacyTypo3Classname($classname) || $this->isLegacyMappingClassname($classname);
    }

    /**
     * Checks whether a mapping exists for the given $classname,
     * indicating it's legacy.
     *
     * @param string $classname
     * @return bool
     */
    public function isCaseInsensitiveLegacyClassname($classname)
    {
        $lowerVersion = strtolower($classname);

        return $this->isLegacyTypo3Classname($classname) || $this->isLegacyMappingClassname($classname)
            || $this->isLegacyTypo3Classname($lowerVersion) || $this->isLegacyMappingClassname($lowerVersion);
    }

    /**
     * @param string $classname
     * @return string
     */
    public function getNewClassname($classname)
    {
        if ($this->isLegacyTypo3Classname($classname) || $this->isLegacyTypo3Classname(strtolower($classname))) {
            return $this->typo3Mappings[$this->getTypo3MappingKey($classname)];
        }

        return $this->mappings[$this->getLegacyMappingKey($classname)];
    }

    /**
     * @param string $classname
     * @return string
     */
    protected function getTypo3MappingKey($classname)
    {
        return $this->typo3MappingsKeys[strtolower($classname)];
    }

    /**
     * @param string $classname
     * @return string
     */
    protected function getLegacyMappingKey($classname)
    {
        return $this->mappingsKeys[strtolower($classname)];
    }

    /**
     * @param string $classname
     * @return bool
     */
    protected function isLegacyTypo3Classname($classname)
    {
        return isset($this->typo3Mappings[$classname]) || isset($this->typo3MappingsKeys[$classname]);
    }

    /**
     * @param string $classname
     * @return bool
     */
    protected function isLegacyMappingClassname($classname)
    {
        return isset($this->mappings[$classname]) || isset($this->mappingsKeys[$classname]);
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
        $this->mappings[$legacyClassname] = $newClassname;
        $this->mappingsKeys[strtolower($legacyClassname)] = $legacyClassname;
        $this->dirty = true;
    }

    public function persistMappings()
    {
        if ($this->dirty === false) {
            return;
        }

        file_put_contents(
            Options::getMappingFile(),
            '<?php' . PHP_EOL . 'return ' . var_export($this->mappings, true) . ';' . PHP_EOL
        );
        $this->dirty = false;
    }

    /**
     * Used to persist new mappings.
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        $this->persistMappings();
    }
}
