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

use Symfony\Component\Yaml\Yaml;

class RemovedByYamlConfiguration
{
    /**
     * Configuration to define removed code.
     *
     * @var array
     */
    protected $configured = [];

    public function __construct(array $configFiles, $prepareStructure)
    {
        foreach ($configFiles as $file) {
            $this->configured = array_merge(
                $this->configured,
                $prepareStructure(Yaml::parse(file_get_contents((string) $file)))
            );
        }
    }

    public function isRemoved($identifier)
    {
        return isset($this->configured[$identifier]);
    }

    public function getAllRemoved()
    {
        return $this->configured;
    }

    public function getRemoved($identifier)
    {
        if (!$this->isRemoved($identifier)) {
            throw new \Exception(
                sprintf('Identifier "%s" is not configured to be removed.', $identifier),
                1493289133
            );
        }

        return $this->configured[$identifier];
    }
}
